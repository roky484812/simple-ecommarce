<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
    ) {}

    /**
     * Send the order confirmation email to the customer.
     */
    public function handle(): void
    {
        // TODO: Build a Mailable class and send to $this->order->user->email
        Log::info("Order confirmation email dispatched for order #{$this->order->order_number}");
    }
}
