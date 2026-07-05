<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderStatusChangedNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public string $status,
    ) {}

    /**
     * Notify the customer that their order's status has changed.
     */
    public function handle(): void
    {
        // TODO: Build a Mailable/Notification class and send to $this->order->user->email
        Log::info("Order status changed notification dispatched for order #{$this->order->order_number}: now {$this->status}");
    }
}
