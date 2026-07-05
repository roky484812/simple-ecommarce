<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a paginated list of the authenticated user's own orders.
     */
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('storefront.orders.index', compact('orders'));
    }

    /**
     * Display a single order's line items, totals, and status timeline.
     * Authorization is enforced via OrderPolicy — only the owner or an admin may view.
     */
    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items.product.images', 'statusHistories', 'payments']);

        return view('storefront.orders.show', compact('order'));
    }
}
