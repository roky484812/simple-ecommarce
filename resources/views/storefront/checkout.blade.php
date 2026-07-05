@extends('layouts.storefront')

@section('title', 'Checkout')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-storefront.breadcrumbs :items="[['label' => 'Cart', 'url' => route('cart.index')], ['label' => 'Checkout']]" />

        <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Shipping Address Selection --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-xl border border-base-200 bg-base-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Shipping Address</h2>

                        @if ($addresses->isEmpty())
                            <div class="text-center py-8">
                                <p class="text-gray-500 mb-4">You don't have any saved addresses.</p>
                                <x-ui.button as="a" :href="route('profile.edit', ['tab' => 'addresses'])" variant="primary" size="sm">
                                    Add an address
                                </x-ui.button>
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($addresses as $address)
                                    <label
                                        class="relative flex cursor-pointer rounded-xl border-2 p-4 transition-colors focus-within:ring-2 focus-within:ring-brand-500 {{ $loop->first ? 'border-brand-500 bg-brand-50' : 'border-base-200 hover:border-brand-300' }}"
                                        x-data
                                        @click="$el.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-brand-500', 'bg-brand-50'); l.classList.add('border-base-200'); }); $el.classList.add('border-brand-500', 'bg-brand-50'); $el.classList.remove('border-base-200');"
                                    >
                                        <input
                                            type="radio"
                                            name="address_id"
                                            value="{{ $address->id }}"
                                            class="sr-only"
                                            {{ $loop->first ? 'checked' : '' }}
                                            required
                                        >
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-medium text-gray-900">{{ $address->label }}</span>
                                                @if ($address->is_default)
                                                    <x-ui.badge variant="brand" size="sm">Default</x-ui.badge>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600">{{ $address->line1 }}</p>
                                            @if ($address->line2)
                                                <p class="text-sm text-gray-600">{{ $address->line2 }}</p>
                                            @endif
                                            <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                                            <p class="text-sm text-gray-500">{{ $address->country }}</p>
                                        </div>
                                        <div class="absolute top-3 right-3">
                                            <svg class="size-5 text-brand-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" x-show="$el.closest('label').querySelector('input').checked" x-cloak>
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            @error('address_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    {{-- Order Items Summary --}}
                    <div class="rounded-xl border border-base-200 bg-base-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Order Items</h2>

                        <div class="space-y-3">
                            @foreach ($lines as $line)
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-lg bg-base-200 overflow-hidden shrink-0">
                                        @if ($line['product']->images->isNotEmpty())
                                            <img
                                                src="{{ $line['product']->images->first()->url() }}"
                                                alt="{{ $line['product']->name }}"
                                                loading="lazy"
                                                class="w-full h-full object-cover"
                                            >
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $line['product']->name }}</p>
                                        <p class="text-xs text-gray-500">Qty: {{ $line['qty'] }} × <x-ui.money :value="$line['price_snapshot']" /></p>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 shrink-0"><x-ui.money :value="$line['line_total']" /></p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Order Total Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="rounded-xl border border-base-200 bg-base-100 p-6 sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span><x-ui.money :value="$subtotal" /></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Shipping</span>
                                <span><x-ui.money :value="$shipping" /></span>
                            </div>
                        </div>

                        <div class="border-t border-base-200 my-4"></div>

                        <div class="flex justify-between font-semibold text-gray-900 mb-6">
                            <span>Total</span>
                            <span><x-ui.money :value="$total" /></span>
                        </div>

                        <x-ui.button
                            type="submit"
                            variant="primary"
                            size="lg"
                            class="w-full"
                            :disabled="$addresses->isEmpty()"
                        >
                            Pay with SSLCommerz
                        </x-ui.button>

                        <p class="text-xs text-gray-500 text-center mt-3">
                            You will be redirected to SSLCommerz to complete payment.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
