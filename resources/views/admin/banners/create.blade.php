@extends('layouts.admin')

@section('title', 'New Banner')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">New Banner</h1>
        <p class="text-sm text-gray-600">Add a new carousel banner for the homepage.</p>
    </div>

    <x-ui.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            @include('admin.banners._form', ['banner' => null])

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button as="a" href="{{ route('admin.banners.index') }}" variant="ghost">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="primary">Create Banner</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
