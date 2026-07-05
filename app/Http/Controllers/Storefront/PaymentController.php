<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Payment;
use App\Services\SslCommerzService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private SslCommerzService $sslCommerzService,
    ) {}

    /**
     * SSLCommerz redirects the customer here after successful payment.
     * This is a POST from SSLCommerz — process it, then redirect to a GET page.
     */
    public function success(Request $request): RedirectResponse
    {
        $payment = $this->findPayment($request);

        if (! $payment) {
            return redirect()->route('home')->with('error', 'Payment not found.');
        }

        if (! $payment->isPaid()) {
            $valId = $request->input('val_id');

            if ($valId) {
                $this->confirmPayment($payment, $valId, $request->all());
            }
        }

        return redirect()->route('payment.result', ['tran_id' => $payment->transaction_id, 'status' => 'success']);
    }

    /**
     * SSLCommerz redirects the customer here when payment fails.
     */
    public function fail(Request $request): RedirectResponse
    {
        $payment = $this->findPayment($request);

        if (! $payment) {
            return redirect()->route('home')->with('error', 'Payment not found.');
        }

        $this->markNonPaidOutcome($payment, 'failed', 'Payment failed. Customer may retry.', $request->all());

        return redirect()->route('payment.result', ['tran_id' => $payment->transaction_id, 'status' => 'fail']);
    }

    /**
     * SSLCommerz redirects the customer here when they cancel payment.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $payment = $this->findPayment($request);

        if (! $payment) {
            return redirect()->route('home')->with('error', 'Payment not found.');
        }

        $this->markNonPaidOutcome($payment, 'cancelled', 'Payment cancelled by customer.', $request->all());

        return redirect()->route('payment.result', ['tran_id' => $payment->transaction_id, 'status' => 'cancel']);
    }

    /**
     * GET route — shows the payment result page. The user's session is intact here
     * because this is a normal browser navigation (not a cross-site POST).
     */
    public function result(Request $request): View|RedirectResponse
    {
        $transactionId = $request->query('tran_id');
        $status = $request->query('status', 'success');

        if (! $transactionId) {
            return redirect()->route('home');
        }

        $payment = Payment::with('order')->where('transaction_id', $transactionId)->first();

        if (! $payment) {
            return redirect()->route('home')->with('error', 'Payment not found.');
        }

        $order = $payment->order;

        return match ($status) {
            'fail' => view('storefront.checkout.fail', compact('order')),
            'cancel' => view('storefront.checkout.cancel', compact('order')),
            default => view('storefront.checkout.success', compact('order')),
        };
    }

    /**
     * IPN (Instant Payment Notification) — SSLCommerz calls this webhook
     * independently of the customer's browser redirect.
     */
    public function ipn(Request $request): Response
    {
        $payment = $this->findPayment($request);

        if (! $payment) {
            return response('Payment not found.', 404);
        }

        // Already processed — idempotent
        if ($payment->isPaid()) {
            return response('Already processed.', 200);
        }

        $valId = $request->input('val_id');
        $status = $request->input('status');

        if ($valId && $status === 'VALID') {
            $this->confirmPayment($payment, $valId, $request->all());
        } elseif ($status === 'FAILED') {
            $this->markNonPaidOutcome($payment, 'failed', 'Payment failed (IPN notification).', $request->all());
        } elseif ($status === 'CANCELLED') {
            $this->markNonPaidOutcome($payment, 'cancelled', 'Payment cancelled (IPN notification).', $request->all());
        }

        return response('IPN processed.', 200);
    }

    /**
     * Validate a payment with SSLCommerz's Order Validation API and,
     * if valid, mark the payment/order as paid and dispatch post-payment jobs.
     *
     * The whole confirmation (payment update + order status + history) runs
     * inside a single DB transaction with a row lock on the payment, so a
     * crash mid-way never leaves "money confirmed but order still pending",
     * and concurrent IPN + browser-redirect calls can't double-process.
     */
    private function confirmPayment(Payment $payment, string $valId, array $gatewayResponse): void
    {
        $validation = $this->sslCommerzService->validateTransaction($valId);

        DB::transaction(function () use ($payment, $valId, $gatewayResponse, $validation) {
            // Re-fetch with a row lock inside the transaction so a concurrent
            // IPN/redirect call blocks here instead of racing past the isPaid() check.
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::with('order')->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            // Idempotent check, now safe under the lock.
            if ($lockedPayment->isPaid()) {
                return;
            }

            if ($validation['valid']) {
                $lockedPayment->update([
                    'status' => 'paid',
                    'val_id' => $valId,
                    'gateway_response' => $gatewayResponse,
                    'paid_at' => now(),
                ]);

                $lockedPayment->order->markStatus('processing', 'Payment confirmed via SSLCommerz.');

                SendOrderConfirmationEmail::dispatch($lockedPayment->order);
                GenerateInvoicePdf::dispatch($lockedPayment->order);
            } else {
                $lockedPayment->update([
                    'status' => 'failed',
                    'val_id' => $valId,
                    'gateway_response' => $gatewayResponse,
                ]);

                $lockedPayment->order->markStatus('pending', 'Payment validation failed.');
            }
        });
    }

    /**
     * Find the Payment record by the `tran_id` parameter from the SSLCommerz callback.
     */
    private function findPayment(Request $request): ?Payment
    {
        $transactionId = $request->input('tran_id');

        if (! $transactionId) {
            return null;
        }

        return Payment::with('order.user')->where('transaction_id', $transactionId)->first();
    }

    /**
     * Mark a payment as failed/cancelled and update the order status together,
     * inside a locked transaction so a paid payment can never be overwritten
     * by a late/duplicate fail or cancel callback racing the success path.
     */
    private function markNonPaidOutcome(Payment $payment, string $status, string $note, array $gatewayResponse): void
    {
        DB::transaction(function () use ($payment, $status, $note, $gatewayResponse) {
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::with('order')->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->isPaid()) {
                return;
            }

            $lockedPayment->update([
                'status' => $status,
                'gateway_response' => $gatewayResponse,
            ]);

            $lockedPayment->order->markStatus('pending', $note);
        });
    }
}
