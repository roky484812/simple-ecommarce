@extends('layouts.storefront')

@section('title', 'Order '.$order->order_number)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-storefront.breadcrumbs :items="[
            ['label' => 'My Orders', 'url' => route('orders.index')],
            ['label' => $order->order_number],
        ]" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('M j, Y \a\t g:ia') }}</p>
            </div>

            @php
                $badgeVariant = match ($order->status) {
                    'delivered' => 'green',
                    'shipped', 'processing' => 'blue',
                    'cancelled' => 'red',
                    default => 'neutral',
                };
            @endphp
            <x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($order->status) }}</x-ui.badge>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                {{-- Status Timeline --}}
                <div class="rounded-xl border border-base-200 bg-base-100 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Status</h2>
                    <x-storefront.order-status-timeline :order="$order" />
                </div>

                {{-- Line Items --}}
                <div class="rounded-xl border border-base-200 bg-base-100 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Items</h2>

                    <div class="space-y-4">
                        @foreach ($order->items as $item)
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-lg bg-base-200 overflow-hidden shrink-0">
                                    @if ($item->product?->images->isNotEmpty())
                                        <img
                                            src="{{ $item->product->images->first()->url() }}"
                                            alt="{{ $item->product->name }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover"
                                        >
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900">
                                        @if ($item->product)
                                            <a href="{{ route('products.show', $item->product) }}" class="hover:text-brand-700">{{ $item->product->name }}</a>
                                        @else
                                            Product no longer available
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500">Qty: {{ $item->qty }} &times; <x-ui.money :value="$item->unit_price" /></p>
                                </div>
                                <p class="font-semibold text-gray-900 shrink-0"><x-ui.money :value="$item->lineTotal()" /></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Order Summary Sidebar --}}
            <div class="lg:col-span-1">
                <div class="rounded-xl border border-base-200 bg-base-100 p-6 sticky top-24">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span><x-ui.money :value="$order->subtotal" /></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping</span>
                            <span><x-ui.money :value="$order->shipping" /></span>
                        </div>
                        @if ((float) $order->tax > 0)
                            <div class="flex justify-between">
                                <span>Tax</span>
                                <span><x-ui.money :value="$order->tax" /></span>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-base-200 my-4"></div>

                    <div class="flex justify-between font-semibold text-gray-900 mb-6">
                        <span>Total</span>
                        <span><x-ui.money :value="$order->total" /></span>
                    </div>

                    <h3 class="font-medium text-gray-900 mb-2 text-sm">Shipping Address</h3>
                    <address class="text-sm text-gray-600 not-italic">
                        {{ $order->shipping_address['line1'] ?? '' }}<br>
                        @if (! empty($order->shipping_address['line2']))
                            {{ $order->shipping_address['line2'] }}<br>
                        @endif
                        {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}<br>
                        {{ $order->shipping_address['country'] ?? '' }}
                    </address>
                </div>
            </div>
        </div>
    </div>
@endsection
