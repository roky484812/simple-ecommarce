@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <p class="text-sm text-gray-600">Manage product categories and subcategories.</p>
        </div>

        <x-ui.button as="a" href="{{ route('admin.categories.create') }}" variant="primary">
            + New Category
        </x-ui.button>
    </div>

    <x-ui.card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <x-admin.category-row :category="$category" />
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 py-8">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @foreach ($categories as $category)
        <x-admin.category-delete-modal :category="$category" />
    @endforeach
@endsection
