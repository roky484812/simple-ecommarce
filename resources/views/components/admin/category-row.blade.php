@props([
    'category',
    'depth' => 0,
])

<tr>
    <td class="font-medium text-gray-900">
        <span class="text-gray-400">{{ str_repeat('— ', $depth) }}</span>{{ $category->name }}
    </td>
    <td class="text-gray-500">{{ $category->slug }}</td>
    <td>
        <x-ui.badge :variant="$category->is_active ? 'green' : 'red'">
            {{ $category->is_active ? 'Active' : 'Inactive' }}
        </x-ui.badge>
    </td>
    <td class="text-right space-x-2 whitespace-nowrap">
        <x-ui.button as="a" href="{{ route('admin.categories.edit', $category) }}" variant="secondary" size="sm">
            Edit
        </x-ui.button>
        <x-ui.button
            type="button"
            variant="danger"
            size="sm"
            onclick="document.getElementById('delete-category-{{ $category->id }}').showModal()"
        >
            Delete
        </x-ui.button>
    </td>
</tr>

@foreach ($category->childrenRecursive as $child)
    <x-admin.category-row :category="$child" :depth="$depth + 1" />
@endforeach
