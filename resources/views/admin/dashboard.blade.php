@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-600">Sales and stock overview for the last 30 days.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-admin.stat-card
            label="Sales (30 days)"
            value="৳{{ number_format($totalSales, 2) }}"
            variant="green"
        />

        <x-admin.stat-card
            label="Orders"
            :value="array_sum(array_column($ordersByStatus, 'count'))"
            variant="brand"
        />

        <x-admin.stat-card
            label="Customers"
            :value="$customerCount"
            variant="neutral"
        />

        <x-admin.stat-card
            label="Low Stock Products"
            :value="$lowStockCount"
            variant="{{ $lowStockCount > 0 ? 'red' : 'neutral' }}"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-ui.card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Orders by Status</h2>
            <div
                x-data="ordersByStatusChart(
                    @js(array_map('ucfirst', array_keys($ordersByStatus))),
                    @js(array_column($ordersByStatus, 'count')),
                    @js(array_column($ordersByStatus, 'qty')),
                    @js(array_column($ordersByStatus, 'amount')),
                )"
                wire:ignore
            ></div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Products (by units sold)</h2>
            <div
                x-data="topProductsChart(
                    @js(array_column($topProducts, 'name')),
                    @js(array_column($topProducts, 'units_sold')),
                    @js(array_column($topProducts, 'amount_total')),
                )"
                wire:ignore
            ></div>
        </x-ui.card>
    </div>

    <x-ui.card class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Customers (by amount spent)</h2>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th class="text-right">Orders</th>
                        <th class="text-right">Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topCustomers as $customer)
                        <tr>
                            <td class="text-gray-900">{{ $customer['name'] }}</td>
                            <td class="text-gray-500">{{ $customer['email'] }}</td>
                            <td class="text-right text-gray-500">{{ $customer['orders_count'] }}</td>
                            <td class="text-right text-gray-900"><x-ui.money :value="$customer['total_spent']" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 py-6">No paid orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Trend (last 30 days)</h2>
        <p class="text-sm text-gray-500 mb-4">Daily order count, quantity sold, and revenue.</p>
        <div
            x-data="orderTrendChart(
                @js(array_column($orderTrend, 'date')),
                @js(array_column($orderTrend, 'count')),
                @js(array_column($orderTrend, 'qty')),
                @js(array_column($orderTrend, 'amount')),
            )"
            wire:ignore
        ></div>
    </x-ui.card>
@endsection
