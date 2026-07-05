<?php

namespace App\Observers;

use App\Models\Banner;
use Illuminate\Support\Facades\Cache;

class BannerObserver
{
    /**
     * Handle the Banner "saved" event.
     */
    public function saved(Banner $banner): void
    {
        Cache::forget('storefront:home:banners');
    }

    /**
     * Handle the Banner "deleted" event.
     */
    public function deleted(Banner $banner): void
    {
        Cache::forget('storefront:home:banners');
    }
}
