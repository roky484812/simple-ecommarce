@extends('layouts.storefront')

@section('title', 'My Orders')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-storefront.breadcrumbs :items="[['label' => 'My Orders']]" />

        <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

        @if ($orders->isEmpty())
            <div class="text-center py-16">
                <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p>
                <x-ui.button as="a" :href="route('products.index')" variant="primary">
                    Start Shopping
                </x-ui.button>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    @php
                        $badgeVariant = match ($order->status) {
                            'delivered' => 'green',
                            'shipped', 'processing' => 'blue',
                            'cancelled' => 'red',
                            default => 'neutral',
                        };
                    @endphp
                    <a href="{{ route('orders.show', $order) }}" class="block">
                        <x-ui.card class="hover:border-brand-300 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->created_at->format('M j, Y') }} &middot; {{ $order->items_count }} item(s)</p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($order->status) }}</x-ui.badge>
                                    <span class="font-semibold text-gray-900"><x-ui.money :value="$order->total" /></span>
                                    <svg class="size-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6" /></svg>
                                </div>
                            </div>
                        </x-ui.card>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                <x-ui.pagination-links :paginator="$orders" />
            </div>
        @endif
    </div>
@endsection
