@props(['banner' => null])

<div class="space-y-4">
    <div>
        <label for="title" class="label mb-1">Title <span class="text-gray-400 text-xs">(optional)</span></label>
        <x-ui.input id="title" name="title" :value="old('title', $banner?->title)" placeholder="e.g. Summer Sale" />
        @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="subtitle" class="label mb-1">Subtitle <span class="text-gray-400 text-xs">(optional)</span></label>
        <x-ui.input id="subtitle" name="subtitle" :value="old('subtitle', $banner?->subtitle)" placeholder="e.g. Up to 50% off on selected items" />
        @error('subtitle') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="image" class="label mb-1">Banner Image @unless($banner) <span class="text-red-500">*</span> @endunless</label>

        @if ($banner && $banner->image_path)
            <div class="mb-2">
                <img src="{{ $banner->imageUrl() }}" alt="Current banner" class="h-24 w-auto rounded border border-base-200 object-cover">
                <p class="text-xs text-gray-500 mt-1">Current image — upload a new one to replace it.</p>
            </div>
        @endif

        <input type="file" id="image" name="image" accept="image/*" class="file-input file-input-bordered w-full" />
        @error('image') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="link_url" class="label mb-1">Link URL <span class="text-gray-400 text-xs">(optional)</span></label>
            <x-ui.input id="link_url" name="link_url" :value="old('link_url', $banner?->link_url)" placeholder="/products" />
            @error('link_url') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="link_text" class="label mb-1">Link Text <span class="text-gray-400 text-xs">(optional)</span></label>
            <x-ui.input id="link_text" name="link_text" :value="old('link_text', $banner?->link_text)" placeholder="Shop Now" />
            @error('link_text') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="sort_order" class="label mb-1">Sort Order</label>
            <x-ui.input id="sort_order" name="sort_order" type="number" min="0" :value="old('sort_order', $banner?->sort_order ?? 0)" />
            @error('sort_order') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="checkbox"
                    @checked(old('is_active', $banner?->is_active ?? true))
                >
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>
</div>
