@props(['product'])

<x-ui.modal :id="'delete-product-'.$product->id" title="Delete product?">
    <p class="text-sm text-gray-600">
        Are you sure you want to delete <strong>{{ $product->name }}</strong>?
        This action can be undone by a database restore only.
    </p>

    <x-slot:footer>
        <form method="dialog">
            <x-ui.button type="submit" variant="ghost" size="sm">Cancel</x-ui.button>
        </form>

        <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" variant="danger" size="sm">Delete</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.modal>
