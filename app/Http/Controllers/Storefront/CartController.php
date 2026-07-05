<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    /**
     * Display the current cart. Line data is loaded and mutated live by the
     * `storefront.cart-page` Livewire component; this action only renders
     * the page shell.
     */
    public function index(): View
    {
        return view('storefront.cart');
    }

    /**
     * Add a product to the cart.
     */
    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $product = Product::findOrFail($request->validated('product_id'));

        $this->cartService->add(
            $request->user(),
            $request->session()->getId(),
            $product,
            (int) $request->validated('qty'),
        );

        return redirect()
            ->route('cart.index')
            ->with('success', "{$product->name} added to cart.");
    }

    /**
     * Update the quantity of a cart line.
     */
    public function update(UpdateCartItemRequest $request, string $cartItem): RedirectResponse
    {
        $this->cartService->update(
            $request->user(),
            $request->session()->getId(),
            $cartItem,
            (int) $request->validated('qty'),
        );

        return redirect()
            ->route('cart.index')
            ->with('success', 'Cart updated.');
    }

    /**
     * Remove a line from the cart.
     */
    public function destroy(Request $request, string $cartItem): RedirectResponse
    {
        $this->cartService->remove($request->user(), $request->session()->getId(), $cartItem);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }
}
