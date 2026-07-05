<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RestockNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Product $product) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Low stock alert: product #{$this->product->id} ({$this->product->name}) has {$this->product->stock_qty} unit(s) left, at or below its threshold of {$this->product->low_stock_threshold}.");
    }
}
