@extends('layouts.storefront')

@section('title', 'Your Cart')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-storefront.breadcrumbs :items="[['label' => 'Cart']]" />

        <h1 class="text-2xl font-bold text-gray-900 mb-6">Your Cart</h1>

        <livewire:storefront.cart-page />
    </div>
@endsection
