<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display a paginated list of payment transactions, filterable by
     * status, gateway, and a created-at date range.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $gateway = $request->string('gateway')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $payments = Payment::query()
            ->with('order.user')
            ->when($status, fn ($query, string $status) => $query->where('status', $status))
            ->when($gateway, fn ($query, string $gateway) => $query->where('gateway', $gateway))
            ->when($from, fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'status' => $status,
            'gateway' => $gateway,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Display a single payment transaction, including the raw gateway response.
     */
    public function show(Payment $payment): View
    {
        $payment->load('order.user');

        return view('admin.payments.show', ['payment' => $payment]);
    }
}
