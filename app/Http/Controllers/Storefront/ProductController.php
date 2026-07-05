<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a paginated, filterable listing of active products.
     *
     * Filtering/sorting/pagination is handled client-side by the
     * `storefront.product-filter` Livewire component; this action only
     * renders the page shell.
     */
    public function index(): View
    {
        return view('storefront.products.index');
    }

    /**
     * Display a single product's detail page.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category']);

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with('images')
            ->take(4)
            ->get();

        return view('storefront.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
