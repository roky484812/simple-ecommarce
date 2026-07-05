@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-600">Manage your application name and logo.</p>
    </div>

    <x-ui.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <x-ui.input
                label="App Name"
                name="app_name"
                :value="old('app_name', $appName)"
                required
                hint="This name appears in the browser title bar and sidebar."
            />

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Logo</legend>

                @if ($logoPath)
                    <div class="mb-3 flex items-center gap-4">
                        <img
                            src="{{ Storage::url($logoPath) }}"
                            alt="Current logo"
                            class="h-16 w-auto rounded border border-gray-200 bg-white p-1"
                        />
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remove_logo" value="1" class="checkbox checkbox-sm" />
                            Remove current logo
                        </label>
                    </div>
                @endif

                <input
                    type="file"
                    name="logo"
                    accept="image/png,image/jpeg,image/svg+xml,image/webp"
                    class="file-input w-full"
                />

                @error('logo')
                    <p class="label text-error">{{ $message }}</p>
                @else
                    <p class="label">PNG, JPG, SVG or WebP. Max 2MB.</p>
                @enderror
            </fieldset>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="submit" variant="primary">Save Settings</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
