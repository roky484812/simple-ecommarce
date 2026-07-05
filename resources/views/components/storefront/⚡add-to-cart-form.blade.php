<?php

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Product $product;

    public int $qty = 1;

    /**
     * Add the product to the current visitor's cart (guest or authenticated)
     * without navigating away, then notify via a toast.
     */
    public function addToCart(CartService $cartService): void
    {
        $this->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:'.max(1, $this->product->stock_qty)],
        ]);

        $cartService->add(
            auth()->user(),
            session()->getId(),
            $this->product,
            $this->qty,
        );

        $this->dispatch('toast', message: "{$this->product->name} added to cart.", variant: 'success');
        $this->dispatch('cart-updated');
    }
};
?>

<form wire:submit="addToCart" class="flex items-center gap-3">
    <input
        type="number"
        wire:model="qty"
        min="1"
        max="{{ $product->stock_qty }}"
        class="input w-20"
        @disabled(! $product->isInStock())
        aria-label="Quantity"
    >

    <x-ui.button
        type="submit"
        variant="primary"
        size="lg"
        class="flex-1"
        :disabled="! $product->isInStock()"
        wire:loading.attr="disabled"
        wire:target="addToCart"
    >
        <span wire:loading.remove wire:target="addToCart">
            {{ $product->isInStock() ? 'Add to cart' : 'Out of stock' }}
        </span>
        <span wire:loading wire:target="addToCart">Adding...</span>
    </x-ui.button>

    @error('qty')
        <p class="text-error text-sm">{{ $message }}</p>
    @enderror
</form>
