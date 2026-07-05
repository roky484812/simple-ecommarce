<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private CartService $cartService,
        private StockService $stockService,
    ) {}

    /**
     * Convert the user's current cart into a pending order inside a DB transaction.
     * Snapshots line prices, decrements stock, clears the cart.
     *
     * @param  array{label: string, line1: string, line2: ?string, city: string, state: string, postal_code: string, country: string}  $shippingAddress
     */
    public function createFromCart(User $user, array $shippingAddress, float $shippingCost = 60.00): Order
    {
        return DB::transaction(function () use ($user, $shippingAddress, $shippingCost) {
            $lines = $this->cartService->lines($user, session()->getId());

            if ($lines->isEmpty()) {
                throw new \RuntimeException('Cannot create an order from an empty cart.');
            }

            $subtotal = (float) $lines->sum('line_total');
            $tax = 0.00;
            $total = $subtotal + $tax + $shippingCost;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shippingCost,
                'total' => $total,
                'shipping_address' => $shippingAddress,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'qty' => $line['qty'],
                    'unit_price' => $line['price_snapshot'],
                ]);

                $this->stockService->decrement($line['product'], $line['qty']);
            }

            $order->statusHistories()->create([
                'status' => 'pending',
                'note' => 'Order placed, awaiting payment.',
            ]);

            // Clear the user's cart
            $cart = Cart::where('user_id', $user->id)->first();
            $cart?->items()->delete();

            return $order;
        });
    }
}
