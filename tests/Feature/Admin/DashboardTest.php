<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('non-admin cannot access the dashboard', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->get(route('admin.dashboard'));

    $response->assertStatus(403);
});

test('admin dashboard data matches direct database aggregates', function () {
    $product = Product::factory()->create(['stock_qty' => 50, 'low_stock_threshold' => 5]);
    Product::factory()->lowStock()->create();

    $paidOrder = Order::factory()->create(['status' => 'processing', 'total' => 1000]);
    $paidOrder->items()->create(['product_id' => $product->id, 'qty' => 3, 'unit_price' => $product->price]);

    Order::factory()->create(['status' => 'pending', 'total' => 500]);
    Order::factory()->create(['status' => 'cancelled', 'total' => 200]);

    $response = $this->actingAs(admin())->get(route('admin.dashboard'));

    $response->assertStatus(200);

    $expectedSales = Order::whereIn('status', ['processing', 'shipped', 'delivered'])->sum('total');
    $expectedLowStock = Product::whereColumn('stock_qty', '<=', 'low_stock_threshold')->count();

    $response->assertSee(number_format((float) $expectedSales, 2));
    $response->assertSee((string) $expectedLowStock);
    $response->assertSee($product->name);
});

test('admin dashboard caches plain arrays, not eloquent model instances, to survive cache serialization', function () {
    $product = Product::factory()->create(['price' => 100]);
    $order = Order::factory()->create(['status' => 'processing', 'total' => 1000]);
    $order->items()->create(['product_id' => $product->id, 'qty' => 2, 'unit_price' => 100]);

    $this->actingAs(admin())->get(route('admin.dashboard'));

    $cached = Cache::get('admin:dashboard');

    expect($cached['topProducts'])->toBeArray();
    expect($cached['topProducts'][0])->toBeArray();
    expect($cached['topProducts'][0]['name'])->toBe($product->name);
    expect($cached['topProducts'][0]['amount_total'])->toBe(200.0);
});

test('admin dashboard orders-by-status includes quantity and amount for every status', function () {
    $product = Product::factory()->create(['price' => 250]);
    $order = Order::factory()->create(['status' => 'processing', 'total' => 1000]);
    $order->items()->create(['product_id' => $product->id, 'qty' => 4, 'unit_price' => 250]);

    $this->actingAs(admin())->get(route('admin.dashboard'));

    $cached = Cache::get('admin:dashboard');

    expect($cached['ordersByStatus'])->toBe([
        'pending' => ['count' => 0, 'qty' => 0, 'amount' => 0.0],
        'processing' => ['count' => 1, 'qty' => 4, 'amount' => 1000.0],
        'shipped' => ['count' => 0, 'qty' => 0, 'amount' => 0.0],
        'delivered' => ['count' => 0, 'qty' => 0, 'amount' => 0.0],
        'cancelled' => ['count' => 0, 'qty' => 0, 'amount' => 0.0],
    ]);
});

test('admin dashboard results are cached and invalidated on order changes', function () {
    Order::factory()->create(['status' => 'processing', 'total' => 1000]);

    $this->actingAs(admin())->get(route('admin.dashboard'));

    expect(Cache::has('admin:dashboard'))->toBeTrue();

    Order::factory()->create(['status' => 'processing', 'total' => 2000]);

    expect(Cache::has('admin:dashboard'))->toBeFalse();
});

test('admin dashboard order trend covers the last 30 days with correct daily counts', function () {
    $product = Product::factory()->create(['price' => 100]);

    $today = Order::factory()->create(['status' => 'processing', 'total' => 1000, 'created_at' => now()]);
    $today->items()->create(['product_id' => $product->id, 'qty' => 4, 'unit_price' => 100]);

    $fiveDaysAgo = Order::factory()->create(['status' => 'processing', 'total' => 500, 'created_at' => now()->subDays(5)]);
    $fiveDaysAgo->items()->create(['product_id' => $product->id, 'qty' => 2, 'unit_price' => 100]);

    $this->actingAs(admin())->get(route('admin.dashboard'));

    $cached = Cache::get('admin:dashboard');

    expect($cached['orderTrend'])->toHaveCount(30);
    expect(collect($cached['orderTrend'])->sum('count'))->toBe(2);
    expect(collect($cached['orderTrend'])->sum('qty'))->toBe(6);
    expect(collect($cached['orderTrend'])->sum('amount'))->toBe(1500.0);
    expect(collect($cached['orderTrend'])->last())->toMatchArray([
        'date' => now()->format('Y-m-d'),
        'count' => 1,
        'qty' => 4,
        'amount' => 1000.0,
    ]);
});

test('admin dashboard ranks top customers by total amount spent on paid orders', function () {
    $bigSpender = User::factory()->create(['name' => 'Big Spender']);
    $smallSpender = User::factory()->create(['name' => 'Small Spender']);

    Order::factory()->create(['user_id' => $bigSpender->id, 'status' => 'processing', 'total' => 5000]);
    Order::factory()->create(['user_id' => $bigSpender->id, 'status' => 'delivered', 'total' => 3000]);
    Order::factory()->create(['user_id' => $smallSpender->id, 'status' => 'processing', 'total' => 100]);

    // Unpaid order should not count towards a customer's total spend.
    Order::factory()->create(['user_id' => $smallSpender->id, 'status' => 'cancelled', 'total' => 9000]);

    $response = $this->actingAs(admin())->get(route('admin.dashboard'));

    $cached = Cache::get('admin:dashboard');

    expect($cached['topCustomers'][0])->toMatchArray([
        'name' => 'Big Spender',
        'orders_count' => 2,
        'total_spent' => 8000.0,
    ]);
    expect($cached['topCustomers'][1])->toMatchArray([
        'name' => 'Small Spender',
        'orders_count' => 1,
        'total_spent' => 100.0,
    ]);

    $response->assertSee('Big Spender');
});
