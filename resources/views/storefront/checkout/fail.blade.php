@extends('layouts.storefront')

@section('title', 'Payment Failed')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="rounded-xl border border-base-200 bg-base-100 p-8">
            <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-red-100 mb-6">
                <svg class="size-8 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m15 9-6 6" />
                    <path d="m9 9 6 6" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
            <p class="text-gray-600 mb-6">
                Unfortunately, your payment could not be processed. Your order has been saved — you can try again or contact support.
            </p>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Order Number</span>
                    <span class="font-medium text-gray-900">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Status</span>
                    <x-ui.badge variant="red">{{ ucfirst($order->status) }}</x-ui.badge>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Total</span>
                    <span class="font-semibold text-gray-900"><x-ui.money :value="$order->total" /></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <x-ui.button as="a" :href="route('cart.index')" variant="ghost">
                    Back to Cart
                </x-ui.button>
                <x-ui.button as="a" :href="route('home')" variant="primary">
                    Continue Shopping
                </x-ui.button>
            </div>
        </div>
    </div>
@endsection
