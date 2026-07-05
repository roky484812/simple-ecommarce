<?php

namespace App\Services;

use App\Jobs\RestockNotification;
use App\Models\Product;

class StockService
{
    /**
     * Decrement a product's stock quantity, dispatching a restock notification
     * job if this decrement crosses the product's low-stock threshold.
     */
    public function decrement(Product $product, int $qty): Product
    {
        $wasAboveThreshold = ! $product->isLowStock();

        $product->decrement('stock_qty', $qty);
        $product->refresh();

        if ($wasAboveThreshold && $product->isLowStock()) {
            RestockNotification::dispatch($product);
        }

        return $product;
    }

    /**
     * Increment a product's stock quantity (e.g. restocking or order cancellation).
     */
    public function increment(Product $product, int $qty): Product
    {
        $product->increment('stock_qty', $qty);
        $product->refresh();

        return $product;
    }
}
