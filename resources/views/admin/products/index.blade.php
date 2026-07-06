@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Products</h1>
            <p class="text-sm text-gray-600">Manage the product catalog, images and stock.</p>
        </div>

        <x-ui.button as="a" href="{{ route('admin.products.create') }}" variant="primary" class="sm:w-auto w-full">
            + New Product
        </x-ui.button>
    </div>

    <livewire:admin.product-filter />
@endsection
