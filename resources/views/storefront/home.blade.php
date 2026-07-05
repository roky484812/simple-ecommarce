@extends('layouts.storefront')

@section('title', 'Home')

@section('content')
    <!-- Hero banner -->
    <section class="bg-gradient-to-br from-brand-700 to-brand-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
            <h1 class="text-3xl sm:text-5xl font-bold tracking-tight">Shop smarter, live better</h1>
            <p class="mt-4 text-brand-100 max-w-xl mx-auto">
                Discover the latest arrivals and best deals, delivered fast across Bangladesh.
            </p>
            <div class="mt-8">
                <x-ui.button as="a" href="{{ route('products.index') }}" variant="primary" size="lg" class="bg-white text-brand-800 hover:bg-brand-50 border-none">
                    Shop now
                </x-ui.button>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

        <!-- Category quick links -->
        @if ($categories->isNotEmpty())
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Shop by category</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('products.index', ['category' => $category->slug]) }}"
                            class="card bg-base-100 border border-base-200 p-4 text-center hover:border-brand-400 hover:shadow-sm transition"
                        >
                            <span class="text-sm font-medium text-gray-800">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Featured / on-sale products -->
        @if ($featuredProducts->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Featured deals</h2>
                    <a href="{{ route('products.index') }}" class="text-sm font-medium text-brand-700 hover:underline">View all</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($featuredProducts as $product)
                        <x-storefront.product-card :product="$product" />
                    @endforeach
                </div>
            </section>
        @endif

        <!-- New arrivals -->
        @if ($newArrivals->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">New arrivals</h2>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="text-sm font-medium text-brand-700 hover:underline">View all</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($newArrivals as $product)
                        <x-storefront.product-card :product="$product" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($featuredProducts->isEmpty() && $newArrivals->isEmpty())
            <x-ui.alert variant="info">No products available yet. Check back soon!</x-ui.alert>
        @endif
    </div>
@endsection
