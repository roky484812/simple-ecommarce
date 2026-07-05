<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * "Paid" order statuses that count towards sales figures.
     *
     * @var array<int, string>
     */
    private const PAID_STATUSES = ['processing', 'shipped', 'delivered'];

    /**
     * All order statuses, in display order, used to guarantee every status
     * appears in the orders-by-status chart even when its count is zero.
     *
     * @var array<int, string>
     */
    private const ALL_STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    /**
     * Display the admin dashboard with sales/stock aggregates fetched
     * concurrently and cached for a short period.
     */
    public function index(): View
    {
        $data = Cache::remember('admin:dashboard', now()->addMinutes(5), function () {
            $rangeStart = now()->subDays(29)->startOfDay();

            [$totalSales, $orderStatusStats, $orderStatusQty, $topProducts, $lowStockCount, $customerCount, $dailyOrderStats, $dailyQtySold, $topCustomers] = Concurrency::run([
                fn () => (float) Order::query()
                    ->whereIn('status', self::PAID_STATUSES)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->sum('total'),

                fn () => Order::query()
                    ->select('status', DB::raw('count(*) as total'), DB::raw('sum(total) as amount_total'))
                    ->groupBy('status')
                    ->get()
                    ->map(fn ($row) => [
                        'status' => $row->status,
                        'total' => (int) $row->total,
                        'amount_total' => (float) $row->amount_total,
                    ])
                    ->keyBy('status')
                    ->all(),

                fn () => OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->select('orders.status', DB::raw('sum(order_items.qty) as qty_total'))
                    ->groupBy('orders.status')
                    ->pluck('qty_total', 'status')
                    ->all(),

                fn () => OrderItem::query()
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->select(
                        'products.id',
                        'products.name',
                        DB::raw('sum(order_items.qty) as units_sold'),
                        DB::raw('sum(order_items.qty * order_items.unit_price) as amount_total'),
                    )
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('units_sold')
                    ->limit(5)
                    ->get()
                    ->map(fn ($row) => [
                        'id' => $row->id,
                        'name' => $row->name,
                        'units_sold' => (int) $row->units_sold,
                        'amount_total' => (float) $row->amount_total,
                    ])
                    ->all(),

                fn () => Product::query()
                    ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                    ->count(),

                fn () => User::query()->where('role', 'customer')->count(),

                fn () => Order::query()
                    ->where('created_at', '>=', $rangeStart)
                    ->select(
                        DB::raw('DATE(created_at) as day'),
                        DB::raw('count(*) as orders_total'),
                        DB::raw('sum(total) as amount_total'),
                    )
                    ->groupBy('day')
                    ->get()
                    ->map(fn ($row) => [
                        'day' => $row->day,
                        'orders_total' => (int) $row->orders_total,
                        'amount_total' => (float) $row->amount_total,
                    ])
                    ->keyBy('day')
                    ->all(),

                fn () => OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.created_at', '>=', $rangeStart)
                    ->select(
                        DB::raw('DATE(orders.created_at) as day'),
                        DB::raw('sum(order_items.qty) as qty_total'),
                    )
                    ->groupBy('day')
                    ->pluck('qty_total', 'day')
                    ->all(),

                fn () => Order::query()
                    ->join('users', 'users.id', '=', 'orders.user_id')
                    ->whereIn('orders.status', self::PAID_STATUSES)
                    ->select(
                        'users.id',
                        'users.name',
                        'users.email',
                        DB::raw('count(orders.id) as orders_count'),
                        DB::raw('sum(orders.total) as total_spent'),
                    )
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderByDesc('total_spent')
                    ->limit(5)
                    ->get()
                    ->map(fn ($row) => [
                        'id' => $row->id,
                        'name' => $row->name,
                        'email' => $row->email,
                        'orders_count' => (int) $row->orders_count,
                        'total_spent' => (float) $row->total_spent,
                    ])
                    ->all(),
            ]);

            $ordersByStatus = collect(self::ALL_STATUSES)
                ->mapWithKeys(fn (string $status) => [
                    $status => [
                        'count' => (int) ($orderStatusStats[$status]['total'] ?? 0),
                        'qty' => (int) ($orderStatusQty[$status] ?? 0),
                        'amount' => (float) ($orderStatusStats[$status]['amount_total'] ?? 0),
                    ],
                ])
                ->all();

            $orderTrend = collect(range(0, 29))
                ->map(fn (int $daysAgo) => now()->subDays(29 - $daysAgo)->format('Y-m-d'))
                ->map(fn (string $day) => [
                    'date' => $day,
                    'count' => (int) ($dailyOrderStats[$day]['orders_total'] ?? 0),
                    'qty' => (int) ($dailyQtySold[$day] ?? 0),
                    'amount' => (float) ($dailyOrderStats[$day]['amount_total'] ?? 0),
                ])
                ->all();

            return [
                'totalSales' => $totalSales,
                'ordersByStatus' => $ordersByStatus,
                'topProducts' => $topProducts,
                'lowStockCount' => $lowStockCount,
                'customerCount' => $customerCount,
                'orderTrend' => $orderTrend,
                'topCustomers' => $topCustomers,
            ];
        });

        return view('admin.dashboard', $data);
    }
}
