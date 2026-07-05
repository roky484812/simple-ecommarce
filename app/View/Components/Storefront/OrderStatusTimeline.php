<?php

namespace App\View\Components\Storefront;

use App\Models\Order;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class OrderStatusTimeline extends Component
{
    /**
     * The canonical order status progression, used to render the
     * "future" steps that don't have a history entry yet.
     *
     * @var array<int, string>
     */
    private const STATUS_STEPS = ['pending', 'processing', 'shipped', 'delivered'];

    /**
     * The ordered list of timeline steps to render, merging the order's
     * actual `order_status_histories` entries with any remaining canonical
     * steps (shown as upcoming) — unless the order was cancelled.
     *
     * @var Collection<int, array{status: string, note: ?string, happened_at: ?Carbon, completed: bool}>
     */
    public Collection $timelineSteps;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Order $order,
    ) {
        $this->timelineSteps = $this->buildSteps();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.storefront.order-status-timeline');
    }

    /**
     * Build the ordered list of timeline steps.
     *
     * @return Collection<int, array{status: string, note: ?string, happened_at: ?Carbon, completed: bool}>
     */
    private function buildSteps(): Collection
    {
        $history = $this->order->statusHistories->keyBy('status');

        if ($this->order->status === 'cancelled') {
            return $history->values()->map(fn ($entry) => [
                'status' => $entry->status,
                'note' => $entry->note,
                'happened_at' => $entry->created_at,
                'completed' => true,
            ]);
        }

        return collect(self::STATUS_STEPS)->map(function (string $status) use ($history) {
            $entry = $history->get($status);

            return [
                'status' => $status,
                'note' => $entry?->note,
                'happened_at' => $entry?->created_at,
                'completed' => $entry !== null,
            ];
        });
    }
}
