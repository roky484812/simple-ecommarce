<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderObserver
{
    /**
     * Handle the Order "saved" event.
     */
    public function saved(Order $order): void
    {
        $this->flushCache();
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        $this->flushCache();
    }

    /**
     * Forget the cached admin dashboard aggregates.
     */
    private function flushCache(): void
    {
        Cache::forget('admin:dashboard');
    }
}
