<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url(as: 'category')]
    public string $categorySlug = '';

    #[Url(as: 'min_price')]
    public string $minPrice = '';

    #[Url(as: 'max_price')]
    public string $maxPrice = '';

    #[Url]
    public string $sort = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategorySlug(): void
    {
        $this->resetPage();
    }

    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categorySlug', 'minPrice', 'maxPrice', 'sort']);
        $this->resetPage();
    }

    public function rendering(View $view): void
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['images', 'category']);

        if ($this->categorySlug) {
            $category = Category::query()->where('slug', $this->categorySlug)->first();

            if ($category) {
                $query->whereIn('category_id', [$category->id, ...$category->descendantIds()]);
            }
        }

        if ($this->minPrice !== '') {
            $query->where('price', '>=', (float) $this->minPrice);
        }

        if ($this->maxPrice !== '') {
            $query->where('price', '<=', (float) $this->maxPrice);
        }

        if ($this->search !== '') {
            $query->where('name', 'like', "%{$this->search}%");
        }

        match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'newest' => $query->latest(),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(12);

        $categories = Cache::remember('storefront:categories:tree', now()->addHour(), function () {
            return Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with(['children' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('name')
                ->get();
        });

        $view->with([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6 lg:hidden">
        <h1 class="text-2xl font-bold text-gray-900">Products</h1>
        <button type="button" class="btn btn-sm" x-data @click="$dispatch('open-mobile-filters')">
            Filters
        </button>
    </div>

    <div class="lg:grid lg:grid-cols-4 lg:gap-8" x-data="{ filtersOpen: false }" x-on:open-mobile-filters.window="filtersOpen = true">
        <!-- Desktop sidebar -->
        <aside class="hidden lg:block lg:col-span-1">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Products</h1>
            @include('components.storefront.partials.product-filter-fields', ['scope' => 'desktop'])
        </aside>

        <!-- Mobile off-canvas filters -->
        <div
            x-show="filtersOpen"
            x-cloak
            class="fixed inset-0 z-90 lg:hidden"
            role="dialog"
            aria-modal="true"
        >
            <div class="absolute inset-0 bg-black/50" @click="filtersOpen = false"></div>
            <div class="absolute top-0 start-0 h-full max-w-xs w-full bg-white overflow-y-auto">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                    <span class="text-lg font-bold text-gray-900">Filters</span>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                        aria-label="Close filters"
                        @click="filtersOpen = false"
                    >
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <div class="p-4">
                    @include('components.storefront.partials.product-filter-fields', ['scope' => 'mobile'])
                </div>
            </div>
        </div>

        <div class="lg:col-span-3" wire:loading.class="opacity-50">
            <p class="text-sm text-gray-500 mb-4">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} found</p>

            @if ($products->isEmpty())
                <x-ui.alert variant="info">No products match your filters. Try adjusting them.</x-ui.alert>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 sm:gap-6">
                    @foreach ($products as $product)
                        <x-storefront.product-card :product="$product" wire:key="product-{{ $product->id }}" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
