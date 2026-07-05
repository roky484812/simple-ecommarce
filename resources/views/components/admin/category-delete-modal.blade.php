@props(['category'])

<x-ui.modal :id="'delete-category-'.$category->id" title="Delete category?">
    @if ($category->childrenRecursive->isNotEmpty())
        <x-ui.alert variant="error">
            <strong>{{ $category->name }}</strong> cannot be deleted because it has subcategories.
            Remove or reassign its subcategories first.
        </x-ui.alert>
    @else
        <p class="text-sm text-gray-600">
            Are you sure you want to delete <strong>{{ $category->name }}</strong>?
            This action can be undone by a database restore only.
        </p>
    @endif

    <x-slot:footer>
        <form method="dialog">
            <x-ui.button type="submit" variant="ghost" size="sm">Cancel</x-ui.button>
        </form>

        @if ($category->childrenRecursive->isEmpty())
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" size="sm">Delete</x-ui.button>
            </form>
        @endif
    </x-slot:footer>
</x-ui.modal>

@foreach ($category->childrenRecursive as $child)
    <x-admin.category-delete-modal :category="$child" />
@endforeach
