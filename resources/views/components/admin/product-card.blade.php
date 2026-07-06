@props(['product'])

<x-ui.card class="p-4">
    <div class="flex items-start gap-3">
        @if ($product->images->isNotEmpty())
            <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="w-14 h-14 object-cover rounded-lg border border-base-200 shrink-0">
        @else
            <div class="w-14 h-14 rounded-lg bg-base-200 flex items-center justify-center text-gray-400 text-xs shrink-0">N/A</div>
        @endif

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <div class="font-medium text-gray-900 truncate">{{ $product->name }}</div>
                    <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                </div>
                <x-ui.badge :variant="$product->is_active ? 'green' : 'red'">
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                </x-ui.badge>
            </div>

            <div class="mt-2 text-sm text-gray-500">{{ $product->category->name }}</div>

            <div class="mt-2 flex items-center justify-between gap-2">
                <div>
                    @if ($product->sale_price && $product->sale_price < $product->price)
                        <span class="line-through text-gray-400 text-sm"><x-ui.money :value="$product->price" /></span>
                        <span class="font-medium text-gray-900"><x-ui.money :value="$product->sale_price" /></span>
                    @else
                        <span class="text-gray-900"><x-ui.money :value="$product->price" /></span>
                    @endif
                </div>
                <x-ui.badge :variant="$product->isInStock() ? ($product->isLowStock() ? 'yellow' : 'green') : 'red'">
                    {{ $product->stock_qty }} in stock
                </x-ui.badge>
            </div>
        </div>
    </div>

    <div class="mt-3 flex items-center gap-2">
        <x-ui.button as="a" href="{{ route('admin.products.edit', $product) }}" variant="secondary" size="sm" class="flex-1">
            Edit
        </x-ui.button>
        <x-ui.button
            type="button"
            variant="danger"
            size="sm"
            class="flex-1"
            onclick="document.getElementById('delete-product-{{ $product->id }}').showModal()"
        >
            Delete
        </x-ui.button>
    </div>
</x-ui.card>
