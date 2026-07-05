<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categoryOptions = Category::query()->orderBy('name')->pluck('name', 'id');

        return view('admin.products.create', ['categoryOptions' => $categoryOptions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->safe()->except(['images', 'remove_images']));

        $this->storeUploadedImages($product, $request);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $categoryOptions = Category::query()->orderBy('name')->pluck('name', 'id');

        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categoryOptions' => $categoryOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->safe()->except(['images', 'remove_images', 'image_order']));

        if ($imageIds = $request->safe()->array('remove_images')) {
            $product->images()->whereIn('id', $imageIds)->get()->each(fn (ProductImage $image) => $image->delete());
        }

        $this->storeUploadedImages($product, $request);

        if ($orderedIds = $request->safe()->array('image_order')) {
            $this->reorderImages($product, $orderedIds);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Store any uploaded images for the given product, appending after existing sort order.
     */
    private function storeUploadedImages(Product $product, StoreProductRequest|UpdateProductRequest $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $nextSortOrder = (int) $product->images()->max('sort_order');

        foreach ($request->file('images') as $image) {
            $nextSortOrder++;

            $product->images()->create([
                'path' => $image->store('products', 'public'),
                'sort_order' => $nextSortOrder,
            ]);
        }
    }

    /**
     * Persist a new display order for the product's images. `$orderedIds`
     * lists surviving image IDs in the order they should be displayed; the
     * first one becomes the thumbnail.
     *
     * @param  array<int, int|string>  $orderedIds
     */
    private function reorderImages(Product $product, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $position => $imageId) {
            $product->images()->whereKey($imageId)->update(['sort_order' => $position]);
        }
    }
}
