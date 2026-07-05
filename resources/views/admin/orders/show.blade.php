@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
    @php
        $badgeVariant = match ($order->status) {
            'delivered' => 'green',
            'shipped', 'processing' => 'blue',
            'cancelled' => 'red',
            default => 'neutral',
        };
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-600">
                Placed on {{ $order->created_at->format('M j, Y \a\t g:ia') }} by
                @if ($order->user)
                    <a href="{{ route('admin.customers.show', $order->user) }}" class="text-brand-700 hover:underline">{{ $order->user->name }}</a>
                @else
                    Guest
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($order->status) }}</x-ui.badge>
            <x-ui.button
                type="button"
                variant="primary"
                size="sm"
                onclick="document.getElementById('update-status-modal').showModal()"
            >
                Update status
            </x-ui.button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <h2 class="font-semibold text-gray-900 mb-4">Order Status</h2>
                <x-storefront.order-status-timeline :order="$order" />
            </x-ui.card>

            <x-ui.card>
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
                                    {{ $item->product?->name ?? 'Product no longer available' }}
                                </p>
                                <p class="text-sm text-gray-500">Qty: {{ $item->qty }} &times; <x-ui.money :value="$item->unit_price" /></p>
                            </div>
                            <p class="font-semibold text-gray-900 shrink-0"><x-ui.money :value="$item->lineTotal()" /></p>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <x-ui.card>
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
            </x-ui.card>

            @if ($order->payments->isNotEmpty())
                <x-ui.card>
                    <h2 class="font-semibold text-gray-900 mb-4">Payments</h2>

                    <div class="space-y-3">
                        @foreach ($order->payments as $payment)
                            <div class="text-sm border-b border-base-200 last:border-0 pb-3 last:pb-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-gray-900">{{ ucfirst($payment->gateway) }}</span>
                                    <x-ui.badge :variant="$payment->isPaid() ? 'green' : 'yellow'">{{ ucfirst($payment->status) }}</x-ui.badge>
                                </div>
                                <p class="text-gray-500">{{ $payment->transaction_id }}</p>
                                <p class="text-gray-900 font-medium"><x-ui.money :value="$payment->amount" /></p>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif
        </div>
    </div>

    <x-ui.modal id="update-status-modal" title="Update order status">
        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" id="update-status-form">
            @csrf
            @method('PATCH')

            <x-ui.select
                name="status"
                label="Status"
                :value="$order->status"
                :options="['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled']"
            />

            <x-ui.textarea name="note" label="Note (optional)" placeholder="Add a note for this status change" />
        </form>

        <x-slot:footer>
            <form method="dialog">
                <x-ui.button type="submit" variant="ghost" size="sm">Cancel</x-ui.button>
            </form>
            <x-ui.button type="submit" form="update-status-form" variant="primary" size="sm">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@endsection
