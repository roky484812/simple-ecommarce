<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

beforeEach(function () {
    Redis::flushdb();
});

test('guest add-to-cart persists in redis and survives across requests', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $this->withSession([])->post(route('cart.store'), [
        'product_id' => $product->id,
        'qty' => 2,
    ])->assertRedirect(route('cart.index'));

    $response = $this->get(route('cart.index'));

    $response->assertStatus(200);
    $response->assertSee($product->name);
    $response->assertSee('2', false);

    $this->assertDatabaseCount('carts', 0);
    $this->assertDatabaseCount('cart_items', 0);
});

test('cannot add more than stock_qty to cart', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 3]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'qty' => 10,
    ]);

    $response = $this->get(route('cart.index'));

    $response->assertSee('3', false);
});

test('authenticated user add-to-cart is stored in the database', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $this->actingAs($user)->post(route('cart.store'), [
        'product_id' => $product->id,
        'qty' => 2,
    ])->assertRedirect(route('cart.index'));

    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'qty' => 2,
    ]);
});

test('cart service merges guest cart into db cart without duplicate items', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);
    $sessionId = 'test-session-id';

    // Existing DB cart item for the user before logging in.
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price_snapshot' => $product->price,
    ]);

    $cartService = app(CartService::class);
    $cartService->add(null, $sessionId, $product, 2);
    $cartService->mergeGuestCartIntoUser($user, $sessionId);

    $this->assertDatabaseHas('cart_items', [
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 3,
    ]);

    expect($cart->items()->count())->toBe(1);
    expect($cartService->lines(null, $sessionId))->toBeEmpty();
});

test('login event listener merges the guest cart bound to the current session', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $this->withSession([]);
    $sessionId = session()->getId();

    app(CartService::class)->add(null, $sessionId, $product, 2);

    event(new Login('web', $user, false));

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'qty' => 2,
    ]);
});

test('cart update route changes quantity', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $item = $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price_snapshot' => $product->price,
    ]);

    $this->actingAs($user)->patch(route('cart.update', $item->id), ['qty' => 5]);

    expect($item->refresh()->qty)->toBe(5);
});

test('cart destroy route removes the line', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $item = $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price_snapshot' => $product->price,
    ]);

    $this->actingAs($user)->delete(route('cart.destroy', $item->id));

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});
