<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
    ) {}

    /**
     * Generate the PDF invoice for the given order.
     */
    public function handle(): void
    {
        // TODO: Generate PDF using a package like DomPDF or similar, store in storage.
        Log::info("Invoice PDF generation dispatched for order #{$this->order->order_number}");
    }
}
