<div class="space-y-6">
    <div>
        <label for="filter-search-{{ $scope }}" class="label mb-1">Search</label>
        <input
            type="search"
            id="filter-search-{{ $scope }}"
            wire:model.live.debounce.300ms="search"
            placeholder="Search products..."
            class="input w-full"
        >
    </div>

    <div>
        <p class="label mb-2">Category</p>
        <div class="space-y-1 max-h-56 overflow-y-auto">
            <label class="flex items-center gap-2 text-sm">
                <input type="radio" name="category-{{ $scope }}" wire:model.live="categorySlug" value="" class="radio radio-sm">
                All categories
            </label>
            @foreach ($categories as $category)
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="category-{{ $scope }}" wire:model.live="categorySlug" value="{{ $category->slug }}" class="radio radio-sm">
                    {{ $category->name }}
                </label>
                @foreach ($category->children as $child)
                    <label class="flex items-center gap-2 text-sm pl-4">
                        <input type="radio" name="category-{{ $scope }}" wire:model.live="categorySlug" value="{{ $child->slug }}" class="radio radio-sm">
                        {{ $child->name }}
                    </label>
                @endforeach
            @endforeach
        </div>
    </div>

    <div>
        <p class="label mb-2">Price range (৳)</p>
        <div class="flex items-center gap-2">
            <input type="number" wire:model.live.debounce.500ms="minPrice" min="0" step="0.01" placeholder="Min" class="input input-sm w-full">
            <span class="text-gray-400">–</span>
            <input type="number" wire:model.live.debounce.500ms="maxPrice" min="0" step="0.01" placeholder="Max" class="input input-sm w-full">
        </div>
    </div>

    <div>
        <label for="filter-sort-{{ $scope }}" class="label mb-1">Sort by</label>
        <select id="filter-sort-{{ $scope }}" wire:model.live="sort" class="select w-full">
            <option value="">Name (A–Z)</option>
            <option value="newest">Newest</option>
            <option value="price_asc">Price: Low to High</option>
            <option value="price_desc">Price: High to Low</option>
        </select>
    </div>

    @if ($search || $categorySlug || $minPrice !== '' || $maxPrice !== '' || $sort)
        <x-ui.button type="button" wire:click="resetFilters" variant="ghost" class="w-full">
            Reset filters
        </x-ui.button>
    @endif
</div>
