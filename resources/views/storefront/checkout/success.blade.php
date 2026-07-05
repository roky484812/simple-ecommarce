@extends('layouts.storefront')

@section('title', 'Payment Successful')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="rounded-xl border border-base-200 bg-base-100 p-8">
            <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-green-100 mb-6">
                <svg class="size-8 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
            <p class="text-gray-600 mb-6">
                Thank you for your order. Your payment has been received and your order is now being processed.
            </p>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Order Number</span>
                    <span class="font-medium text-gray-900">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Status</span>
                    <x-ui.badge variant="green">{{ ucfirst($order->status) }}</x-ui.badge>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Total</span>
                    <span class="font-semibold text-gray-900"><x-ui.money :value="$order->total" /></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <x-ui.button as="a" :href="route('products.index')" variant="ghost">
                    Continue Shopping
                </x-ui.button>
                <x-ui.button as="a" :href="route('profile.edit', ['tab' => 'orders'])" variant="primary">
                    View My Orders
                </x-ui.button>
            </div>
        </div>
    </div>
@endsection
