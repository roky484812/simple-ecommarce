<?php

use App\Services\CartService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
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
     * Remove a line from the cart entirely.
     */
    public function remove(string $lineId): void
    {
        app(CartService::class)->remove(auth()->user(), session()->getId(), $lineId);

        unset($this->lines, $this->subtotal);

        $this->dispatch('toast', message: 'Item removed from cart.', variant: 'success');
        $this->dispatch('cart-updated');
    }

    /**
     * Re-fetch cart lines whenever the cart changes elsewhere on the page
     * (e.g. an add-to-cart form or the full cart page).
     */
    #[On('cart-updated')]
    public function refresh(): void
    {
        unset($this->lines, $this->subtotal);
    }
};
?>

<div x-data="{ open: false }" x-on:open-cart-drawer.window="open = true">
    <!-- Backdrop -->
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/50 z-90"
        @click="open = false"
    ></div>

    <!-- Drawer panel -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 end-0 h-full max-w-sm w-full bg-white z-90 shadow-xl flex flex-col"
        role="dialog"
        aria-modal="true"
        aria-label="Cart"
        @keydown.escape.window="open = false"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <span class="text-lg font-bold text-gray-900">Your Cart</span>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                aria-label="Close cart"
                @click="open = false"
            >
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            @if ($this->lines->isEmpty())
                <p class="text-center text-gray-500 py-12">Your cart is empty.</p>
            @else
                @foreach ($this->lines as $line)
                    <div class="flex items-center gap-3" wire:key="drawer-line-{{ $line['id'] }}">
                        <a href="{{ route('products.show', $line['product']) }}" class="w-14 h-14 rounded-lg bg-base-200 overflow-hidden shrink-0" @click="open = false">
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
                            <a href="{{ route('products.show', $line['product']) }}" class="text-sm font-medium text-gray-900 hover:text-brand-700 line-clamp-2" @click="open = false">
                                {{ $line['product']->name }}
                            </a>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $line['qty'] }} × <x-ui.money :value="$line['price_snapshot']" />
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="remove('{{ $line['id'] }}')"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-error hover:bg-red-50 shrink-0"
                            aria-label="Remove {{ $line['product']->name }}"
                        >
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6" /></svg>
                        </button>
                    </div>
                @endforeach
            @endif
        </div>

        @if ($this->lines->isNotEmpty())
            <div class="border-t border-gray-200 p-4 space-y-3">
                <div class="flex justify-between font-semibold text-gray-900">
                    <span>Subtotal</span>
                    <span><x-ui.money :value="$this->subtotal" /></span>
                </div>

                <x-ui.button as="a" :href="route('cart.index')" variant="primary" size="lg" class="w-full" @click="open = false">
                    View cart
                </x-ui.button>
            </div>
        @endif
    </div>
</div>
