@props([
    'product' => null,
    'categoryOptions' => [],
])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main fields --}}
    <div class="lg:col-span-2">
        <x-ui.card class="space-y-4">
            <h3 class="font-semibold text-gray-900 mb-1">General</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.input
                    label="Name"
                    name="name"
                    placeholder="e.g. Wireless Mouse"
                    value="{{ old('name', $product?->name) }}"
                    required
                    autofocus
                />

                <x-ui.input
                    label="SKU"
                    name="sku"
                    placeholder="e.g. SKU-00123"
                    value="{{ old('sku', $product?->sku) }}"
                    required
                />
            </div>

            <x-ui.input
                label="Slug"
                name="slug"
                placeholder="auto-generated-from-name"
                value="{{ old('slug', $product?->slug) }}"
                hint="Leave blank to auto-generate from the name."
            />

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Category</legend>
                <select name="category_id" id="category_id" class="select w-full @error('category_id') select-error @enderror" required>
                    <option value="" disabled @selected(! old('category_id', $product?->category_id))>— Select a category —</option>
                    @foreach ($categoryOptions as $id => $name)
                        <option value="{{ $id }}" @selected((int) old('category_id', $product?->category_id) === $id)>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.input
                    label="Price (৳)"
                    name="price"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    value="{{ old('price', $product?->price) }}"
                    required
                />

                <x-ui.input
                    label="Sale Price (৳)"
                    name="sale_price"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="Optional"
                    value="{{ old('sale_price', $product?->sale_price) }}"
                    hint="Optional. Must be lower than price."
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.input
                    label="Stock Quantity"
                    name="stock_qty"
                    type="number"
                    min="0"
                    placeholder="0"
                    value="{{ old('stock_qty', $product?->stock_qty ?? 0) }}"
                    required
                />

                <x-ui.input
                    label="Low Stock Threshold"
                    name="low_stock_threshold"
                    type="number"
                    min="0"
                    placeholder="5"
                    value="{{ old('low_stock_threshold', $product?->low_stock_threshold ?? 5) }}"
                    required
                />
            </div>

            <fieldset class="fieldset">
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="hidden" name="is_active" value="0" />
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="checkbox"
                        {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}
                    />
                    <span class="fieldset-legend">Active</span>
                </label>
            </fieldset>
        </x-ui.card>
    </div>

    {{-- Description + images --}}
    <div class="space-y-6">
        <x-ui.card>
            <h3 class="font-semibold text-gray-900 mb-3">Description</h3>

            <div
                x-data="richTextEditor('description', @js(old('description', $product?->description ?? '')))"
            >
                <div x-ref="editor" class="bg-white" style="min-height: 12rem;"></div>
                <textarea x-ref="input" name="description" class="hidden"></textarea>
            </div>
            @error('description')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-gray-900 mb-3">Product Images</h3>

            <div
                class="grid grid-cols-3 sm:grid-cols-4 gap-3"
                x-data="productImagePicker()"
            >
                @if ($product)
                    @foreach ($product->images as $index => $image)
                        <div
                            class="group relative aspect-square rounded-lg overflow-hidden border border-base-200"
                            x-data="{ removed: false }"
                            :class="{ 'opacity-40': removed }"
                        >
                            <img
                                src="{{ $image->url() }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover"
                            />

                            @if ($index === 0)
                                <span class="product-image-main-badge absolute bottom-0 inset-x-0 bg-black/70 text-white text-[11px] text-center py-1">
                                    Main
                                </span>
                            @endif

                            <input type="hidden" name="image_order[]" value="{{ $image->id }}" />

                            {{-- Delete: hidden until hover on desktop, always visible on touch devices --}}
                            <label
                                class="absolute top-1 right-1 flex items-center justify-center w-6 h-6 rounded-full bg-white/90 shadow cursor-pointer
                                    opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus-within:opacity-100 transition-opacity"
                                title="Remove this image"
                            >
                                <input
                                    type="checkbox"
                                    name="remove_images[]"
                                    value="{{ $image->id }}"
                                    class="hidden"
                                    x-model="removed"
                                />
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-gray-700">
                                    <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                                </svg>
                            </label>

                            <button
                                type="button"
                                class="absolute top-1 left-1 w-6 h-6 rounded-full bg-white/90 shadow flex items-center justify-center
                                    opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-opacity"
                                style="{{ $index === 0 ? 'display: none' : '' }}"
                                title="Move left"
                                onclick="moveProductImage(this, -1)"
                            >
                                <span class="text-xs leading-none text-gray-700">‹</span>
                            </button>

                            <button
                                type="button"
                                class="absolute top-1 {{ $index === 0 ? 'left-1' : 'left-8' }} w-6 h-6 rounded-full bg-white/90 shadow flex items-center justify-center
                                    opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-opacity"
                                style="{{ $index === $product->images->count() - 1 ? 'display: none' : '' }}"
                                title="Move right"
                                onclick="moveProductImage(this, 1)"
                            >
                                <span class="text-xs leading-none text-gray-700">›</span>
                            </button>
                        </div>
                    @endforeach
                @endif

                {{-- Newly-picked (not yet uploaded) file previews --}}
                <template x-for="(file, index) in pendingFiles" :key="file.__id">
                    <div class="group relative aspect-square rounded-lg overflow-hidden border border-base-200">
                        <img :src="file.__previewUrl" class="w-full h-full object-cover" />

                        <button
                            type="button"
                            class="absolute top-1 right-1 flex items-center justify-center w-6 h-6 rounded-full bg-white/90 shadow cursor-pointer
                                opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-opacity"
                            title="Remove this file"
                            @click="removePendingFile(index)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-gray-700">
                                <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                </template>

                {{-- Add More tile --}}
                <label
                    for="images"
                    class="aspect-square rounded-lg border-2 border-dashed border-base-300 flex flex-col items-center justify-center gap-1 text-gray-400 hover:text-primary hover:border-primary cursor-pointer transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2.5M16 6l-4-4-4 4M12 2v14" />
                    </svg>
                    <span class="text-xs font-medium">Add More</span>
                    <input
                        type="file"
                        name="images[]"
                        id="images"
                        multiple
                        accept="image/*"
                        class="hidden"
                        x-ref="input"
                        @change="filesPicked($event)"
                    />
                </label>
            </div>

            @error('images.*')
                <p class="label text-error">{{ $message }}</p>
            @enderror

            @if ($product && $product->images->isNotEmpty())
                <p class="text-xs text-gray-500 mt-2">Hover an image to remove it or change its order. The first image is the thumbnail shown to customers.</p>
            @endif
        </x-ui.card>
    </div>
</div>

<script>
    /**
     * Moves an image tile left/right within the grid by swapping it with its
     * neighboring tile, then refreshes the "Main" badge and the move
     * button visibility/positioning for the affected tiles.
     */
    function moveProductImage(button, direction) {
        const tile = button.closest('[x-data]');
        const grid = tile.parentNode;
        const sibling = direction < 0 ? tile.previousElementSibling : tile.nextElementSibling;

        // The grid's last child is always the "Add More" tile — never swap past it.
        if (! sibling || ! sibling.hasAttribute('x-data')) {
            return;
        }

        if (direction < 0) {
            grid.insertBefore(tile, sibling);
        } else {
            grid.insertBefore(sibling, tile);
        }

        refreshProductImageTiles(grid);
    }

    /**
     * Re-derive each image tile's "Main" badge and move-button
     * visibility/positions after a reorder.
     */
    function refreshProductImageTiles(grid) {
        const tiles = Array.from(grid.children).filter((el) => el.hasAttribute('x-data'));

        tiles.forEach((tile, index) => {
            let mainBadge = tile.querySelector('.product-image-main-badge');

            if (index === 0 && ! mainBadge) {
                mainBadge = document.createElement('span');
                mainBadge.className = 'product-image-main-badge absolute bottom-0 inset-x-0 bg-black/70 text-white text-[11px] text-center py-1';
                mainBadge.textContent = 'Main';
                tile.appendChild(mainBadge);
            } else if (index !== 0 && mainBadge) {
                mainBadge.remove();
            }

            const moveLeftBtn = tile.querySelector('[title="Move left"]');
            const moveRightBtn = tile.querySelector('[title="Move right"]');

            if (moveLeftBtn) {
                moveLeftBtn.style.display = index === 0 ? 'none' : '';
            }

            if (moveRightBtn) {
                moveRightBtn.style.display = index === tiles.length - 1 ? 'none' : '';
                moveRightBtn.classList.toggle('left-1', index === 0);
                moveRightBtn.classList.toggle('left-8', index !== 0);
            }
        });
    }
</script>
