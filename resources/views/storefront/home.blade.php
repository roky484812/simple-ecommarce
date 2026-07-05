@extends('layouts.storefront')

@section('title', 'Home')

@section('content')
    <!-- Banner carousel -->
    @if ($banners->isNotEmpty())
        <section
            x-data="{
                active: 0,
                total: {{ $banners->count() }},
                interval: null,
                start() { this.interval = setInterval(() => this.next(), 5000) },
                stop() { clearInterval(this.interval) },
                next() { this.active = (this.active + 1) % this.total },
            }"
            x-init="start()"
            x-on:mouseenter="stop()"
            x-on:mouseleave="start()"
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6"
            aria-roledescription="carousel"
            aria-label="Homepage banners"
        >
            <div class="relative aspect-[3/1] sm:aspect-[3.5/1] lg:aspect-[4/1] bg-base-200 rounded-xl overflow-hidden">
                @foreach ($banners as $index => $banner)
                    <div
                        x-show="active === {{ $index }}"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-cloak
                        class="absolute inset-0"
                        role="group"
                        aria-roledescription="slide"
                        aria-label="Slide {{ $index + 1 }} of {{ $banners->count() }}"
                    >
                        <img
                            src="{{ $banner->imageUrl() }}"
                            alt="{{ $banner->title ?? 'Banner ' . ($index + 1) }}"
                            class="w-full h-full object-cover"
                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        >

                        @if ($banner->title || $banner->subtitle || $banner->link_url)
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent flex items-center">
                                <div class="px-6 sm:px-10 lg:px-12 w-full">
                                    <div class="max-w-lg">
                                        @if ($banner->title)
                                            <h2 class="text-2xl sm:text-4xl lg:text-5xl font-bold text-white drop-shadow-lg">{{ $banner->title }}</h2>
                                        @endif
                                        @if ($banner->subtitle)
                                            <p class="mt-2 text-sm sm:text-lg text-white/90">{{ $banner->subtitle }}</p>
                                        @endif
                                        @if ($banner->link_url)
                                            <div class="mt-4">
                                                <x-ui.button as="a" href="{{ $banner->link_url }}" variant="primary" size="lg">
                                                    {{ $banner->link_text ?? 'Shop Now' }}
                                                </x-ui.button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

                <!-- Dots -->
                @if ($banners->count() > 1)
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-2">
                        @foreach ($banners as $index => $banner)
                            <button
                                type="button"
                                @click="active = {{ $index }}"
                                class="size-2.5 rounded-full transition-colors duration-200"
                                :class="active === {{ $index }} ? 'bg-white' : 'bg-white/50'"
                                aria-label="Go to slide {{ $index + 1 }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @else
        <!-- Fallback static hero when no banners exist -->
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
    @endif

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
