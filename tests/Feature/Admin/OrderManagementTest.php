<?php

use App\Jobs\SendOrderStatusChangedNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('non-admin cannot access order management', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->get(route('admin.orders.index'));

    $response->assertStatus(403);
});

test('admin can view the orders list', function () {
    Order::factory()->create();
    Order::factory()->create();
    Order::factory()->create();

    $response = $this->actingAs(admin())->get(route('admin.orders.index'));

    $response->assertStatus(200);
});

test('admin can filter orders by status', function () {
    $pending = Order::factory()->create(['status' => 'pending']);
    $shipped = Order::factory()->create(['status' => 'shipped']);

    $response = $this->actingAs(admin())->get(route('admin.orders.index', ['status' => 'shipped']));

    $response->assertSee($shipped->order_number);
    $response->assertDontSee($pending->order_number);
});

test('admin can view an order detail page', function () {
    $order = Order::factory()->create();

    $response = $this->actingAs(admin())->get(route('admin.orders.show', $order));

    $response->assertStatus(200);
    $response->assertSee($order->order_number);
});

test('admin can change an order status', function () {
    Queue::fake();

    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->actingAs(admin())->patch(route('admin.orders.update-status', $order), [
        'status' => 'processing',
        'note' => 'Payment confirmed manually.',
    ]);

    $response->assertRedirect(route('admin.orders.show', $order));

    expect($order->refresh()->status)->toBe('processing');

    $this->assertDatabaseHas('order_status_histories', [
        'order_id' => $order->id,
        'status' => 'processing',
        'note' => 'Payment confirmed manually.',
    ]);

    Queue::assertPushed(SendOrderStatusChangedNotification::class, function (SendOrderStatusChangedNotification $job) use ($order) {
        return $job->order->is($order) && $job->status === 'processing';
    });
});

test('changing order status rejects an invalid status value', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->actingAs(admin())->patch(route('admin.orders.update-status', $order), [
        'status' => 'not-a-real-status',
    ]);

    $response->assertSessionHasErrors('status');
    expect($order->refresh()->status)->toBe('pending');
});

test('non-admin cannot change an order status', function () {
    $customer = User::factory()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($customer)->patch(route('admin.orders.update-status', $order), [
        'status' => 'processing',
    ]);

    $response->assertStatus(403);
    expect($order->refresh()->status)->toBe('pending');
});
