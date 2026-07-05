@extends('layouts.storefront')

@section('title', $product->name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-storefront.breadcrumbs :items="[
            ['label' => $product->category->name, 'url' => route('products.index', ['category' => $product->category->slug])],
            ['label' => $product->name],
        ]" />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Image gallery -->
            <div x-data="{ activeImage: 0 }">
                @if ($product->images->isNotEmpty())
                    <div class="relative rounded-xl border border-base-200 overflow-hidden aspect-square bg-base-200">
                        @foreach ($product->images as $index => $image)
                            <img
                                x-show="activeImage === {{ $index }}"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-[1.02]"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                x-cloak
                                src="{{ $image->url() }}"
                                alt="{{ $product->name }}"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                class="absolute inset-0 w-full h-full object-cover"
                            >
                        @endforeach
                    </div>

                    @if ($product->images->count() > 1)
                        <div class="grid grid-cols-5 gap-2 mt-3">
                            @foreach ($product->images as $index => $image)
                                <button
                                    type="button"
                                    @click="activeImage = {{ $index }}"
                                    class="aspect-square rounded-lg overflow-hidden border-2 transition-colors duration-200"
                                    :class="activeImage === {{ $index }} ? 'border-brand-600' : 'border-base-200'"
                                    aria-label="View image {{ $index + 1 }}"
                                >
                                    <img src="{{ $image->url() }}" alt="" loading="lazy" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="aspect-square rounded-xl bg-base-200 flex items-center justify-center text-gray-400">
                        No image available
                    </div>
                @endif
            </div>

            <!-- Details -->
            <div>
                <p class="text-sm text-gray-500">{{ $product->category->name }}</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{{ $product->name }}</h1>

                <div class="flex items-center gap-2 mt-3">
                    <x-storefront.rating-stars :rating="4" />
                    <span class="text-sm text-gray-500">(placeholder rating)</span>
                </div>

                <div class="flex items-center gap-3 mt-4">
                    @if ($product->sale_price && $product->sale_price < $product->price)
                        <span class="text-2xl font-bold text-gray-900"><x-ui.money :value="$product->sale_price" /></span>
                        <span class="text-lg line-through text-gray-400"><x-ui.money :value="$product->price" /></span>
                    @else
                        <span class="text-2xl font-bold text-gray-900"><x-ui.money :value="$product->price" /></span>
                    @endif
                </div>

                <div class="mt-3">
                    @if ($product->isInStock())
                        <x-ui.badge :variant="$product->isLowStock() ? 'yellow' : 'green'">
                            {{ $product->stock_qty }} in stock
                        </x-ui.badge>
                    @else
                        <x-ui.badge variant="red">Out of stock</x-ui.badge>
                    @endif
                </div>

                @if ($product->description)
                    <p class="mt-6 text-gray-600 leading-relaxed">{{ $product->description }}</p>
                @endif

                <!-- Add to cart -->
                <form
                    method="POST"
                    action="{{ Route::has('cart.store') ? route('cart.store') : '#' }}"
                    class="mt-8 flex items-center gap-3"
                >
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <input
                        type="number"
                        name="qty"
                        value="1"
                        min="1"
                        max="{{ $product->stock_qty }}"
                        class="input w-20"
                        @disabled(! $product->isInStock())
                        aria-label="Quantity"
                    >

                    <x-ui.button type="submit" variant="primary" size="lg" class="flex-1" :disabled="! $product->isInStock()">
                        {{ $product->isInStock() ? 'Add to cart' : 'Out of stock' }}
                    </x-ui.button>
                </form>
            </div>
        </div>

        <!-- Related products -->
        @if ($relatedProducts->isNotEmpty())
            <section class="mt-16">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Related products</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($relatedProducts as $related)
                        <x-storefront.product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
