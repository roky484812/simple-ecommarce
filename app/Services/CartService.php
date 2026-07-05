<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class CartService
{
    /**
     * How long a guest cart is retained in Redis without activity.
     */
    private const GUEST_CART_TTL_SECONDS = 60 * 60 * 24 * 14;

    /**
     * Get the authenticated user's DB-backed cart, creating one if needed.
     */
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Get the authenticated user's DB-backed cart without creating one,
     * for read-only operations where an empty result is fine.
     */
    private function findCart(User $user): ?Cart
    {
        return Cart::query()->where('user_id', $user->id)->first();
    }

    /**
     * Add a product to the cart (guest session or authenticated user),
     * clamped to the product's available stock. Returns the resulting qty.
     */
    public function add(?User $user, string $sessionId, Product $product, int $qty): int
    {
        $qty = max(1, $qty);

        if ($user) {
            return $this->addForUser($user, $product, $qty);
        }

        return $this->addForGuest($sessionId, $product, $qty);
    }

    /**
     * Update the quantity of a cart line, clamped to available stock.
     * Removes the line if `$qty` is 0 or less.
     */
    public function update(?User $user, string $sessionId, string|int $itemId, int $qty): void
    {
        if ($user) {
            $this->updateForUser($user, (int) $itemId, $qty);

            return;
        }

        $this->updateForGuest($sessionId, (string) $itemId, $qty);
    }

    /**
     * Remove a single line from the cart.
     */
    public function remove(?User $user, string $sessionId, string|int $itemId): void
    {
        if ($user) {
            $this->removeForUser($user, (int) $itemId);

            return;
        }

        $this->removeForGuest($sessionId, (string) $itemId);
    }

    /**
     * Get the cart lines as a unified collection of arrays, each shaped:
     * `['id', 'product' => Product, 'qty' => int, 'price_snapshot' => float, 'line_total' => float]`.
     *
     * @return Collection<int, array{id: string|int, product: Product, qty: int, price_snapshot: float, line_total: float}>
     */
    public function lines(?User $user, string $sessionId): Collection
    {
        if ($user) {
            $cart = $this->findCart($user);

            if (! $cart) {
                return collect();
            }

            return $cart
                ->items()
                ->with('product.images')
                ->get()
                ->map(fn (CartItem $item) => [
                    'id' => $item->id,
                    'product' => $item->product,
                    'qty' => $item->qty,
                    'price_snapshot' => (float) $item->price_snapshot,
                    'line_total' => $item->lineTotal(),
                ])
                ->values();
        }

        $guestItems = $this->readGuestCart($sessionId);

        if ($guestItems === []) {
            return collect();
        }

        $products = Product::query()
            ->with('images')
            ->whereIn('id', array_keys($guestItems))
            ->get()
            ->keyBy('id');

        return collect($guestItems)
            ->map(function (array $item, string $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                return [
                    'id' => $productId,
                    'product' => $product,
                    'qty' => $item['qty'],
                    'price_snapshot' => (float) $item['price_snapshot'],
                    'line_total' => (float) $item['price_snapshot'] * $item['qty'],
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * The total quantity of items in the cart (for the navbar badge).
     */
    public function totalQty(?User $user, string $sessionId): int
    {
        return (int) $this->lines($user, $sessionId)->sum('qty');
    }

    /**
     * The subtotal of the cart.
     */
    public function subtotal(?User $user, string $sessionId): float
    {
        return (float) $this->lines($user, $sessionId)->sum('line_total');
    }

    /**
     * Merge a guest's Redis cart into their DB-backed cart on login,
     * summing quantities (clamped to stock) for products present in both,
     * then clearing the guest cart.
     */
    public function mergeGuestCartIntoUser(User $user, string $sessionId): void
    {
        $guestItems = $this->readGuestCart($sessionId);

        if ($guestItems === []) {
            return;
        }

        $cart = $this->getOrCreateCart($user);

        $products = Product::query()->whereIn('id', array_keys($guestItems))->get()->keyBy('id');

        foreach ($guestItems as $productId => $item) {
            $product = $products->get((int) $productId);

            if (! $product) {
                continue;
            }

            /** @var CartItem|null $existing */
            $existing = $cart->items()->where('product_id', $product->id)->first();

            $newQty = min($product->stock_qty, ($existing?->qty ?? 0) + $item['qty']);

            if ($newQty <= 0) {
                continue;
            }

            $cart->items()->updateOrCreate(
                ['product_id' => $product->id],
                ['qty' => $newQty, 'price_snapshot' => $item['price_snapshot']],
            );
        }

        $this->clearGuestCart($sessionId);
    }

    /**
     * Add a product for an authenticated user's DB cart.
     */
    private function addForUser(User $user, Product $product, int $qty): int
    {
        $cart = $this->getOrCreateCart($user);

        /** @var CartItem|null $item */
        $item = $cart->items()->where('product_id', $product->id)->first();

        $newQty = min($product->stock_qty, ($item?->qty ?? 0) + $qty);

        $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['qty' => $newQty, 'price_snapshot' => $product->displayPrice()],
        );

        return $newQty;
    }

    /**
     * Add a product for a guest's Redis cart.
     */
    private function addForGuest(string $sessionId, Product $product, int $qty): int
    {
        $items = $this->readGuestCart($sessionId);

        $existingQty = $items[$product->id]['qty'] ?? 0;
        $newQty = min($product->stock_qty, $existingQty + $qty);

        $items[$product->id] = [
            'qty' => $newQty,
            'price_snapshot' => (float) $product->displayPrice(),
        ];

        $this->writeGuestCart($sessionId, $items);

        return $newQty;
    }

    /**
     * Update the quantity of a user's cart line.
     */
    private function updateForUser(User $user, int $itemId, int $qty): void
    {
        $cart = $this->findCart($user);

        /** @var CartItem|null $item */
        $item = $cart?->items()->whereKey($itemId)->first();

        if (! $item) {
            return;
        }

        if ($qty <= 0) {
            $item->delete();

            return;
        }

        $item->update(['qty' => min($qty, $item->product->stock_qty)]);
    }

    /**
     * Update the quantity of a guest's cart line (keyed by product ID).
     */
    private function updateForGuest(string $sessionId, string $productId, int $qty): void
    {
        $items = $this->readGuestCart($sessionId);

        if (! isset($items[$productId])) {
            return;
        }

        if ($qty <= 0) {
            unset($items[$productId]);
            $this->writeGuestCart($sessionId, $items);

            return;
        }

        $product = Product::find((int) $productId);
        $items[$productId]['qty'] = $product ? min($qty, $product->stock_qty) : $qty;

        $this->writeGuestCart($sessionId, $items);
    }

    /**
     * Remove a line from a user's cart.
     */
    private function removeForUser(User $user, int $itemId): void
    {
        $this->findCart($user)?->items()->whereKey($itemId)->delete();
    }

    /**
     * Remove a line from a guest's cart (keyed by product ID).
     */
    private function removeForGuest(string $sessionId, string $productId): void
    {
        $items = $this->readGuestCart($sessionId);
        unset($items[$productId]);
        $this->writeGuestCart($sessionId, $items);
    }

    /**
     * Read the guest cart's raw item map from Redis:
     * `[product_id => ['qty' => int, 'price_snapshot' => float]]`.
     *
     * @return array<string, array{qty: int, price_snapshot: float}>
     */
    private function readGuestCart(string $sessionId): array
    {
        $raw = Redis::get($this->guestCartKey($sessionId));

        if (! $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Persist the guest cart's raw item map to Redis with a rolling TTL.
     *
     * @param  array<string, array{qty: int, price_snapshot: float}>  $items
     */
    private function writeGuestCart(string $sessionId, array $items): void
    {
        if ($items === []) {
            $this->clearGuestCart($sessionId);

            return;
        }

        Redis::set($this->guestCartKey($sessionId), json_encode($items), 'EX', self::GUEST_CART_TTL_SECONDS);
    }

    /**
     * Delete the guest cart from Redis entirely.
     */
    private function clearGuestCart(string $sessionId): void
    {
        Redis::del($this->guestCartKey($sessionId));
    }

    /**
     * The Redis key a guest's cart is stored under.
     */
    private function guestCartKey(string $sessionId): string
    {
        return "cart:{$sessionId}";
    }
}
