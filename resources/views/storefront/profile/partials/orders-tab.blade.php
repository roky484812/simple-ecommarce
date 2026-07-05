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
                @php
                    $badgeVariant = match ($order->status) {
                        'delivered' => 'green',
                        'shipped', 'processing' => 'blue',
                        'cancelled' => 'red',
                        default => 'neutral',
                    };
                @endphp
                <a href="{{ route('orders.show', $order) }}" class="block">
                    <x-ui.card class="hover:border-brand-300 transition-colors">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold">{{ $order->order_number }}</p>
                                <p class="text-sm text-base-content/70">
                                    {{ $order->created_at->format('M j, Y') }} &middot; {{ $order->items_count }} item(s)
                                </p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="font-semibold"><x-ui.money :value="$order->total" /></span>
                                <x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($order->status) }}</x-ui.badge>
                            </div>
                        </div>
                    </x-ui.card>
                </a>
            @endforeach
        </div>

        <x-ui.button as="a" :href="route('orders.index')" variant="ghost" size="sm">
            View all orders
        </x-ui.button>
    @endif
</section>
