<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createOrderForUser(User $user, string $status = 'processing'): Order
{
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['price' => 500.00]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => $status,
        'subtotal' => 500.00,
        'shipping' => 60.00,
        'total' => 560.00,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 1,
        'unit_price' => 500.00,
    ]);

    $order->statusHistories()->create(['status' => 'pending', 'note' => 'Order placed.']);

    if ($status !== 'pending') {
        $order->statusHistories()->create(['status' => $status, 'note' => 'Payment confirmed.']);
    }

    return $order;
}

test('orders index requires authentication', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

test('customer can view their own order list', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $order = createOrderForUser($user);

    $response = $this->actingAs($user)->get(route('orders.index'));

    $response->assertStatus(200);
    $response->assertSee($order->order_number);
});

test('customer only sees their own orders in the list', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $otherUser = User::factory()->create(['role' => 'customer']);

    $myOrder = createOrderForUser($user);
    $otherOrder = createOrderForUser($otherUser);

    $response = $this->actingAs($user)->get(route('orders.index'));

    $response->assertSee($myOrder->order_number);
    $response->assertDontSee($otherOrder->order_number);
});

test('customer cannot view another customers order', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $otherUser = User::factory()->create(['role' => 'customer']);

    $otherOrder = createOrderForUser($otherUser);

    $this->actingAs($user)
        ->get(route('orders.show', $otherOrder))
        ->assertForbidden();
});

test('customer can view their own order detail', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $order = createOrderForUser($user);

    $response = $this->actingAs($user)->get(route('orders.show', $order));

    $response->assertStatus(200);
    $response->assertSee($order->order_number);
});

test('admin can view any customers order', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $order = createOrderForUser($customer);

    $this->actingAs($admin)
        ->get(route('orders.show', $order))
        ->assertStatus(200);
});

test('order status timeline renders all status_histories entries in order', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $order = createOrderForUser($user, 'shipped');

    // Order has pending -> shipped history entries (from createOrderForUser).
    // Add a processing entry out of insertion order to verify oldest-first rendering.
    $order->statusHistories()->create(['status' => 'processing', 'note' => 'Now processing.']);

    $response = $this->actingAs($user)->get(route('orders.show', $order));

    $response->assertStatus(200);

    $content = $response->getContent();

    // Isolate the timeline's <ol> so status badges elsewhere on the page
    // (e.g. the header badge) don't interfere with ordering assertions.
    preg_match('/<ol class="relative border-s.*?<\/ol>/s', $content, $matches);
    expect($matches)->not->toBeEmpty();
    $timelineHtml = $matches[0];

    $pendingPos = mb_strpos($timelineHtml, 'Pending');
    $processingPos = mb_strpos($timelineHtml, 'Processing');
    $shippedPos = mb_strpos($timelineHtml, 'Shipped');

    expect($pendingPos)->not->toBeFalse();
    expect($processingPos)->not->toBeFalse();
    expect($shippedPos)->not->toBeFalse();

    // Steps must render in canonical order: pending, processing, shipped, delivered.
    expect($pendingPos)->toBeLessThan($processingPos);
    expect($processingPos)->toBeLessThan($shippedPos);
});

test('order detail shows line items and totals', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $order = createOrderForUser($user);

    $response = $this->actingAs($user)->get(route('orders.show', $order));

    $response->assertSee($order->items->first()->product->name);
    $response->assertSee('560.00', false);
});
