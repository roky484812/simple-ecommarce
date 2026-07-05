<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a paginated, searchable list of customers.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $customers = User::query()
            ->where('role', 'customer')
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with('profile')
            ->withCount('orders')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    /**
     * Display a single customer's profile, addresses, and order history.
     */
    public function show(User $customer): View
    {
        $customer->load(['profile', 'addresses']);

        $orders = $customer->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    /**
     * Toggle the blocked status of the given customer.
     */
    public function toggleBlock(User $user): RedirectResponse
    {
        $user->forceFill(['is_blocked' => ! $user->is_blocked])->save();

        return redirect()
            ->route('admin.customers.show', $user)
            ->with('success', $user->is_blocked
                ? "{$user->name} has been blocked and can no longer log in."
                : "{$user->name} has been unblocked.");
    }
}
