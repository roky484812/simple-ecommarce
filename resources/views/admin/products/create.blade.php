@extends('layouts.admin')

@section('title', 'New Product')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-6 bg-gray-50/95 backdrop-blur border-b border-gray-200 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">New Product</h1>
                <p class="text-sm text-gray-600">Add a product to the catalog.</p>
            </div>

            <div class="flex gap-2 shrink-0">
                <x-ui.button as="a" href="{{ route('admin.products.index') }}" variant="ghost">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="primary">Create Product</x-ui.button>
            </div>
        </div>

        @include('admin.products._form', ['product' => null, 'categoryOptions' => $categoryOptions])
    </form>
@endsection
