<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Jobs\SendOrderStatusChangedNotification;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a paginated list of orders, optionally filtered by status.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($status, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    /**
     * Display a single order's line items, totals, and status history.
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'items.product.images', 'statusHistories', 'payments']);

        return view('admin.orders.show', ['order' => $order]);
    }

    /**
     * Update the order's status, recording a history entry and notifying the customer.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order->markStatus($request->string('status')->toString(), $request->string('note')->toString() ?: null);

        SendOrderStatusChangedNotification::dispatch($order, $order->status);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }
}
