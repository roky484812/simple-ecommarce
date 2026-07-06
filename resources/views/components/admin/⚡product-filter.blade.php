<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryId = '';

    public string $stockStatus = '';

    public string $isActive = '';

    public function mount(): void
    {
        $this->search = request()->string('search')->toString();
        $this->categoryId = request()->string('category_id')->toString();
        $this->stockStatus = request()->string('stock_status')->toString();
        $this->isActive = request()->string('is_active')->toString();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedStockStatus(): void
    {
        $this->resetPage();
    }

    public function updatedIsActive(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryId', 'stockStatus', 'isActive']);
        $this->resetPage();
    }

    public function rendering(View $view): void
    {
        $products = Product::query()
            ->with(['category', 'images'])
            ->when($this->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($this->categoryId, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($this->stockStatus, function ($query, string $stockStatus) {
                match ($stockStatus) {
                    'out_of_stock' => $query->where('stock_qty', '<=', 0),
                    'low_stock' => $query->whereColumn('stock_qty', '<=', 'low_stock_threshold')->where('stock_qty', '>', 0),
                    'in_stock' => $query->whereColumn('stock_qty', '>', 'low_stock_threshold'),
                    default => null,
                };
            })
            ->when($this->isActive !== '', fn ($query) => $query->where('is_active', $this->isActive === '1'))
            // ->orderBy('name')
            ->latest()
            ->paginate(15);


        $categoryOptions = Category::query()->orderBy('name')->pluck('name', 'id');

        $view->with([
            'products' => $products,
            'categoryOptions' => $categoryOptions,
        ]);
    }
};
?>

<div>
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:items-end">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Search</legend>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Name or SKU"
                    class="input w-full"
                />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Category</legend>
                <select wire:model.live="categoryId" class="select w-full">
                    <option value="">All categories</option>
                    @foreach ($categoryOptions as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Stock</legend>
                <select wire:model.live="stockStatus" class="select w-full">
                    <option value="">Any stock level</option>
                    <option value="in_stock">In stock</option>
                    <option value="low_stock">Low stock</option>
                    <option value="out_of_stock">Out of stock</option>
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Status</legend>
                <select wire:model.live="isActive" class="select w-full">
                    <option value="">Any status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </fieldset>

            <div class="sm:col-span-2 lg:col-span-1 flex gap-2">
                @if ($search || $categoryId || $stockStatus || $isActive !== '')
                    <x-ui.button type="button" wire:click="resetFilters" variant="ghost" class="flex-1">
                        Reset
                    </x-ui.button>
                @endif
            </div>
        </div>
    </x-ui.card>

    <!-- Mobile card list -->
    <div class="sm:hidden space-y-3">
        @forelse ($products as $product)
            <x-admin.product-card :product="$product" />
        @empty
            <x-ui.card class="text-center text-gray-500 py-8">No products found.</x-ui.card>
        @endforelse
    </div>

    <!-- Desktop table -->
    <x-ui.card class="hidden sm:block p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <x-admin.product-row :product="$product" />
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-8">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    @foreach ($products as $product)
        <x-admin.product-delete-modal :product="$product" />
    @endforeach
</div>
