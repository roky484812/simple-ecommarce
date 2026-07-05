<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the storefront home page.
     */
    public function index(): View
    {
        $featuredProducts = Cache::remember('storefront:home:featured', now()->addMinutes(15), function () {
            return Product::query()
                ->where('is_active', true)
                ->where('stock_qty', '>', 0)
                ->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price')
                ->with('images')
                ->latest()
                ->take(8)
                ->get();
        });

        $newArrivals = Cache::remember('storefront:home:new-arrivals', now()->addMinutes(15), function () {
            return Product::query()
                ->where('is_active', true)
                ->with('images')
                ->latest()
                ->take(8)
                ->get();
        });

        $categories = Cache::remember('storefront:categories:tree', now()->addHour(), function () {
            return Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with(['children' => fn ($query) => $query->where('is_active', true)])
                ->orderBy('name')
                ->get();
        });

        return view('storefront.home', [
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'categories' => $categories,
        ]);
    }
}
