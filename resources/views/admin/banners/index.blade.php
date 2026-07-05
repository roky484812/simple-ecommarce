@extends('layouts.admin')

@section('title', 'Banners')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Banners</h1>
            <p class="text-sm text-gray-600">Manage homepage carousel banners.</p>
        </div>

        <x-ui.button as="a" href="{{ route('admin.banners.create') }}" variant="primary">
            + New Banner
        </x-ui.button>
    </div>

    <x-ui.card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr>
                            <td>
                                <img
                                    src="{{ $banner->imageUrl() }}"
                                    alt="{{ $banner->title ?? 'Banner' }}"
                                    class="h-12 w-24 object-cover rounded"
                                >
                            </td>
                            <td>
                                <span class="font-medium text-gray-900">{{ $banner->title ?? '—' }}</span>
                                @if ($banner->subtitle)
                                    <p class="text-xs text-gray-500">{{ Str::limit($banner->subtitle, 50) }}</p>
                                @endif
                            </td>
                            <td>{{ $banner->sort_order }}</td>
                            <td>
                                @if ($banner->is_active)
                                    <x-ui.badge variant="green">Active</x-ui.badge>
                                @else
                                    <x-ui.badge variant="red">Inactive</x-ui.badge>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button as="a" href="{{ route('admin.banners.edit', $banner) }}" variant="ghost" size="sm">
                                        Edit
                                    </x-ui.button>

                                    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete this banner?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
                                            Delete
                                        </x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-8">No banners yet. Create one to display on the homepage.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@endsection
