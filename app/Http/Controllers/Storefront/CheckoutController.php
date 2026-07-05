<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SslCommerzService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private SslCommerzService $sslCommerzService,
    ) {}

    /**
     * Show the checkout page with address selection and order summary.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $lines = $this->cartService->lines($user, session()->getId());

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $subtotal = $this->cartService->subtotal($user, session()->getId());
        $shipping = 60.00;
        $total = $subtotal + $shipping;

        return view('storefront.checkout', compact('lines', 'addresses', 'subtotal', 'shipping', 'total'));
    }

    /**
     * Create the order and redirect to SSLCommerz payment gateway.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
        ]);

        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->input('address_id'));

        $shippingAddress = [
            'label' => $address->label,
            'line1' => $address->line1,
            'line2' => $address->line2,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
        ];

        try {
            $order = $this->orderService->createFromCart($user, $shippingAddress);
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')
                ->with('error', $e->getMessage());
        }

        $result = $this->sslCommerzService->initiate($order);

        // Create a payment record in "initiated" status
        $order->payments()->create([
            'gateway' => 'sslcommerz',
            'transaction_id' => $result['transaction_id'],
            'amount' => $order->total,
            'status' => 'initiated',
        ]);

        if ($result['success'] && $result['gateway_url']) {
            return redirect()->away($result['gateway_url']);
        }

        // If initiation failed, mark order as failed and redirect back
        $order->markStatus('cancelled', 'Payment gateway initiation failed.');

        return redirect()->route('cart.index')
            ->with('error', 'Could not connect to the payment gateway. Please try again.');
    }
}
