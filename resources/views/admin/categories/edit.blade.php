@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Category</h1>
        <p class="text-sm text-gray-600">Update "{{ $category->name }}".</p>
    </div>

    <x-ui.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-4">
            @csrf
            @method('PUT')

            @include('admin.categories._form', ['category' => $category, 'parentOptions' => $parentOptions])

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button as="a" href="{{ route('admin.categories.index') }}" variant="ghost">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="primary">Save Changes</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
