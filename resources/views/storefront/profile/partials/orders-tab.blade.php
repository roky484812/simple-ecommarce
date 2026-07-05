<section class="space-y-4">
    <div>
        <h2 class="text-lg font-semibold">{{ __('Recent Orders') }}</h2>
        <p class="mt-1 text-sm text-base-content/70">{{ __('Your 5 most recent orders.') }}</p>
    </div>

    @if ($recentOrders->isEmpty())
        <p class="text-sm text-base-content/70">{{ __('You have not placed any orders yet.') }}</p>
    @else
        <div class="space-y-3">
            @foreach ($recentOrders as $order)
                <x-ui.card>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">{{ $order->order_number }}</p>
                            <p class="text-sm text-base-content/70">{{ $order->created_at->format('M j, Y') }}</p>
                        </div>
                        <x-ui.badge variant="neutral">{{ ucfirst($order->status) }}</x-ui.badge>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</section>
