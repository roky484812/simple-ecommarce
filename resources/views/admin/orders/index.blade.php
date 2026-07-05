@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
            <p class="text-sm text-gray-600">View and manage customer orders.</p>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row gap-4 sm:items-end">
            <fieldset class="fieldset flex-1">
                <legend class="fieldset-legend">Status</legend>
                <select name="status" class="select w-full" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </fieldset>

            @if ($status)
                <x-ui.button as="a" href="{{ route('admin.orders.index') }}" variant="ghost">Reset</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $badgeVariant = match ($order->status) {
                                'delivered' => 'green',
                                'shipped', 'processing' => 'blue',
                                'cancelled' => 'red',
                                default => 'neutral',
                            };
                        @endphp
                        <tr>
                            <td class="font-medium text-gray-900">{{ $order->order_number }}</td>
                            <td class="text-gray-500">{{ $order->user?->name ?? 'Guest' }}</td>
                            <td class="text-gray-500">{{ $order->created_at->format('M j, Y') }}</td>
                            <td class="text-gray-500">{{ $order->items_count }}</td>
                            <td class="text-gray-900"><x-ui.money :value="$order->total" /></td>
                            <td><x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($order->status) }}</x-ui.badge></td>
                            <td class="text-right whitespace-nowrap">
                                <x-ui.button as="a" href="{{ route('admin.orders.show', $order) }}" variant="secondary" size="sm">
                                    View
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 py-8">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        <x-ui.pagination-links :paginator="$orders" />
    </div>
@endsection
