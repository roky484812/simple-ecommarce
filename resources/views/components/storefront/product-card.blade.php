@props(['product'])

<a
    href="{{ route('products.show', $product) }}"
    {{ $attributes->class(['group card bg-base-100 shadow-sm border border-base-200 overflow-hidden hover:shadow-md transition-shadow']) }}
>
    <div class="aspect-square bg-base-200 overflow-hidden">
        @if ($product->images->isNotEmpty())
            <img
                src="{{ $product->images->first()->url() }}"
                alt="{{ $product->name }}"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">No image</div>
        @endif
    </div>

    <div class="p-4 space-y-1">
        <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
        <h3 class="font-medium text-gray-900 line-clamp-2">{{ $product->name }}</h3>

        <div class="flex items-center gap-2 pt-1">
            @if ($product->sale_price && $product->sale_price < $product->price)
                <span class="font-semibold text-gray-900"><x-ui.money :value="$product->sale_price" /></span>
                <span class="text-sm line-through text-gray-400"><x-ui.money :value="$product->price" /></span>
            @else
                <span class="font-semibold text-gray-900"><x-ui.money :value="$product->price" /></span>
            @endif
        </div>

        @unless ($product->isInStock())
            <x-ui.badge variant="red" class="mt-1">Out of stock</x-ui.badge>
        @endunless
    </div>
</a>
