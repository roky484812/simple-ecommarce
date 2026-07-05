<?php

use App\Services\CartService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    /**
     * Quantities keyed by line ID (cart_item ID for authenticated users,
     * product ID for guests), bound live to each line's qty input.
     *
     * @var array<string, int>
     */
    public array $quantities = [];

    public function mount(CartService $cartService): void
    {
        $this->syncQuantitiesFromLines($cartService);
    }

    /**
     * The current cart lines, unified regardless of guest/authenticated state.
     *
     * @return Collection<int, array{id: string|int, product: \App\Models\Product, qty: int, price_snapshot: float, line_total: float}>
     */
    #[Computed]
    public function lines(): Collection
    {
        return app(CartService::class)->lines(auth()->user(), session()->getId());
    }

    /**
     * The cart subtotal across all lines.
     */
    #[Computed]
    public function subtotal(): float
    {
        return (float) $this->lines->sum('line_total');
    }

    /**
     * Called automatically by Livewire whenever a `quantities.{id}` input
     * changes (debounced client-side), persisting the new qty immediately
     * without a separate "Update" click.
     */
    public function updatedQuantities(mixed $value, string $key): void
    {
        $qty = max(0, (int) $value);

        app(CartService::class)->update(auth()->user(), session()->getId(), $key, $qty);

        unset($this->lines, $this->subtotal);
        $this->syncQuantitiesFromLines(app(CartService::class));

        $this->dispatch('toast', message: $qty > 0 ? 'Cart updated.' : 'Item removed from cart.', variant: 'success');
        $this->dispatch('cart-updated');
    }

    /**
     * Remove a line from the cart entirely.
     */
    public function remove(string $lineId): void
    {
        app(CartService::class)->remove(auth()->user(), session()->getId(), $lineId);

        unset($this->quantities[$lineId], $this->lines, $this->subtotal);

        $this->dispatch('toast', message: 'Item removed from cart.', variant: 'success');
        $this->dispatch('cart-updated');
    }

    /**
     * Rebuild the `$quantities` map from the current cart lines.
     */
    private function syncQuantitiesFromLines(CartService $cartService): void
    {
        $this->quantities = $cartService
            ->lines(auth()->user(), session()->getId())
            ->mapWithKeys(fn (array $line) => [(string) $line['id'] => $line['qty']])
            ->all();
    }
};
?>

<div>
    @if ($this->lines->isEmpty())
        <div class="text-center py-16">
            <p class="text-gray-500">Your cart is empty.</p>
            <x-ui.button as="a" :href="route('products.index')" variant="primary" class="mt-4">
                Continue shopping
            </x-ui.button>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                @foreach ($this->lines as $line)
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-base-200 bg-base-100" wire:key="cart-line-{{ $line['id'] }}">
                        <a href="{{ route('products.show', $line['product']) }}" class="w-20 h-20 rounded-lg bg-base-200 overflow-hidden shrink-0">
                            @if ($line['product']->images->isNotEmpty())
                                <img
                                    src="{{ $line['product']->images->first()->url() }}"
                                    alt="{{ $line['product']->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover"
                                >
                            @endif
                        </a>

                        <div class="flex-1 min-w-0">
                            <a href="{{ route('products.show', $line['product']) }}" class="font-medium text-gray-900 hover:text-brand-700 line-clamp-2">
                                {{ $line['product']->name }}
                            </a>
                            <p class="text-sm text-gray-500 mt-1"><x-ui.money :value="$line['price_snapshot']" /> each</p>
                        </div>

                        <div wire:loading.class="opacity-50" wire:target="quantities.{{ $line['id'] }}">
                            <input
                                type="number"
                                wire:model.live.debounce.500ms="quantities.{{ $line['id'] }}"
                                min="0"
                                max="{{ $line['product']->stock_qty }}"
                                class="input input-sm w-16"
                                aria-label="Quantity for {{ $line['product']->name }}"
                            >
                        </div>

                        <button
                            type="button"
                            wire:click="remove('{{ $line['id'] }}')"
                            class="p-2 rounded-lg text-gray-400 hover:text-error hover:bg-red-50"
                            aria-label="Remove {{ $line['product']->name }}"
                        >
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6" /></svg>
                        </button>

                        <p class="font-semibold text-gray-900 w-24 text-right shrink-0"><x-ui.money :value="$line['line_total']" /></p>
                    </div>
                @endforeach
            </div>

            <div class="lg:col-span-1">
                <div class="rounded-xl border border-base-200 bg-base-100 p-6 sticky top-24">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Subtotal</span>
                        <span><x-ui.money :value="$this->subtotal" /></span>
                    </div>

                    <div class="border-t border-base-200 my-4"></div>

                    <div class="flex justify-between font-semibold text-gray-900 mb-6">
                        <span>Total</span>
                        <span><x-ui.money :value="$this->subtotal" /></span>
                    </div>

                    <x-ui.button
                        as="a"
                        :href="Route::has('checkout.show') ? route('checkout.show') : '#'"
                        variant="primary"
                        size="lg"
                        class="w-full"
                    >
                        Proceed to checkout
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
