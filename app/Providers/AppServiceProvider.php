<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.storefront', function ($view): void {
            $view->with('navCategories', Cache::remember('storefront:categories:tree', now()->addHour(), function () {
                return Category::query()
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->with(['children' => fn ($query) => $query->where('is_active', true)])
                    ->orderBy('name')
                    ->get();
            }));
        });
    }
}
