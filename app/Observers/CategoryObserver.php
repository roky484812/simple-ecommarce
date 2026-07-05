<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Handle the Category "saved" event.
     */
    public function saved(Category $category): void
    {
        $this->flushCache();
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->flushCache();
    }

    /**
     * Forget cached category tree used across the storefront.
     */
    private function flushCache(): void
    {
        Cache::forget('storefront:categories:tree');
    }
}
