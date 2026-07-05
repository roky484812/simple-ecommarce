<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Handle the Product "saved" event.
     */
    public function saved(Product $product): void
    {
        $this->flushCache();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->flushCache();
    }

    /**
     * Forget cached homepage product listings.
     */
    private function flushCache(): void
    {
        Cache::forget('storefront:home:featured');
        Cache::forget('storefront:home:new-arrivals');
    }
}
