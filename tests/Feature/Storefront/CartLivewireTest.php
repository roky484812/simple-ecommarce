<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Redis::flushdb();
});

test('add to cart form adds the product and dispatches a toast without redirecting', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    Livewire::test('storefront.add-to-cart-form', ['product' => $product])
        ->set('qty', 2)
        ->call('addToCart')
        ->assertDispatched('toast')
        ->assertDispatched('cart-updated')
        ->assertNoRedirect();
});

test('add to cart form clamps quantity to available stock', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 3]);

    Livewire::test('storefront.add-to-cart-form', ['product' => $product])
        ->set('qty', 10)
        ->call('addToCart')
        ->assertHasErrors('qty');
});

test('cart page updates quantity live without an explicit update action', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $item = $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price_snapshot' => $product->price,
    ]);

    Livewire::actingAs($user)
        ->test('storefront.cart-page')
        ->set("quantities.{$item->id}", 4)
        ->assertDispatched('toast')
        ->assertDispatched('cart-updated');

    expect($item->refresh()->qty)->toBe(4);
});

test('cart page removes a line via the remove action', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $item = $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price_snapshot' => $product->price,
    ]);

    Livewire::actingAs($user)
        ->test('storefront.cart-page')
        ->call('remove', (string) $item->id)
        ->assertDispatched('toast');

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

test('cart page setting quantity to zero removes the line', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $item = $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price_snapshot' => $product->price,
    ]);

    Livewire::actingAs($user)
        ->test('storefront.cart-page')
        ->set("quantities.{$item->id}", 0);

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

test('cart drawer shows current cart lines', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 2,
        'price_snapshot' => $product->price,
    ]);

    Livewire::actingAs($user)
        ->test('storefront.cart-drawer')
        ->assertSee($product->name);
});

test('cart drawer removes a line via the remove action', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $item = $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price_snapshot' => $product->price,
    ]);

    Livewire::actingAs($user)
        ->test('storefront.cart-drawer')
        ->call('remove', (string) $item->id)
        ->assertDispatched('toast')
        ->assertDispatched('cart-updated');

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

test('add to cart form does not automatically open the cart drawer', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    Livewire::test('storefront.add-to-cart-form', ['product' => $product])
        ->call('addToCart')
        ->assertNotDispatched('open-cart-drawer');
});

test('storefront navbar shows the current cart item count', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true, 'stock_qty' => 10]);

    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'qty' => 3,
        'price_snapshot' => $product->price,
    ]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertSee('Cart (3 items)', false);
});
