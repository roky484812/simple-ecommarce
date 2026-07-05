@extends('layouts.admin')

@section('title', 'Edit Banner')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Banner</h1>
        <p class="text-sm text-gray-600">Update banner details.</p>
    </div>

    <x-ui.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            @include('admin.banners._form', ['banner' => $banner])

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button as="a" href="{{ route('admin.banners.index') }}" variant="ghost">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="primary">Save Changes</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
