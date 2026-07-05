@props(['product'])

<tr>
    <td class="flex items-center gap-3">
        @if ($product->images->isNotEmpty())
            <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="w-10 h-10 object-cover rounded-lg border border-base-200">
        @else
            <div class="w-10 h-10 rounded-lg bg-base-200 flex items-center justify-center text-gray-400 text-xs">N/A</div>
        @endif
        <div>
            <div class="font-medium text-gray-900">{{ $product->name }}</div>
            <div class="text-xs text-gray-500">{{ $product->sku }}</div>
        </div>
    </td>
    <td class="text-gray-500">{{ $product->category->name }}</td>
    <td>
        @if ($product->sale_price && $product->sale_price < $product->price)
            <span class="line-through text-gray-400"><x-ui.money :value="$product->price" /></span>
            <span class="font-medium text-gray-900"><x-ui.money :value="$product->sale_price" /></span>
        @else
            <span class="text-gray-900"><x-ui.money :value="$product->price" /></span>
        @endif
    </td>
    <td>
        <x-ui.badge :variant="$product->isInStock() ? ($product->isLowStock() ? 'yellow' : 'green') : 'red'">
            {{ $product->stock_qty }} in stock
        </x-ui.badge>
    </td>
    <td>
        <x-ui.badge :variant="$product->is_active ? 'green' : 'red'">
            {{ $product->is_active ? 'Active' : 'Inactive' }}
        </x-ui.badge>
    </td>
    <td class="text-right space-x-2 whitespace-nowrap">
        <x-ui.button as="a" href="{{ route('admin.products.edit', $product) }}" variant="secondary" size="sm">
            Edit
        </x-ui.button>
        <x-ui.button
            type="button"
            variant="danger"
            size="sm"
            onclick="document.getElementById('delete-product-{{ $product->id }}').showModal()"
        >
            Delete
        </x-ui.button>
    </td>
</tr>
