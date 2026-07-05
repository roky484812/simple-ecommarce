<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('home page shows only active products', function () {
    $category = Category::factory()->create();
    $active = Product::factory()->for($category)->create(['is_active' => true]);
    $inactive = Product::factory()->for($category)->create(['is_active' => false]);

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee($active->name);
    $response->assertDontSee($inactive->name);
});

test('product listing filters by category', function () {
    $categoryA = Category::factory()->create();
    $categoryB = Category::factory()->create();
    $productA = Product::factory()->for($categoryA)->create(['is_active' => true]);
    $productB = Product::factory()->for($categoryB)->create(['is_active' => true]);

    $response = $this->get(route('products.index', ['category' => $categoryA->slug]));

    $response->assertStatus(200);
    $response->assertSee($productA->name);
    $response->assertDontSee($productB->name);
});

test('product listing filters by price range', function () {
    $category = Category::factory()->create();
    $cheap = Product::factory()->for($category)->create(['is_active' => true, 'price' => 10]);
    $expensive = Product::factory()->for($category)->create(['is_active' => true, 'price' => 500]);

    $response = $this->get(route('products.index', ['min_price' => 100]));

    $response->assertStatus(200);
    $response->assertSee($expensive->name);
    $response->assertDontSee($cheap->name);
});

test('product listing filters by search term', function () {
    $category = Category::factory()->create();
    $match = Product::factory()->for($category)->create(['is_active' => true, 'name' => 'Wireless Keyboard']);
    $other = Product::factory()->for($category)->create(['is_active' => true, 'name' => 'Cotton T-Shirt']);

    $response = $this->get(route('products.index', ['search' => 'Keyboard']));

    $response->assertStatus(200);
    $response->assertSee($match->name);
    $response->assertDontSee($other->name);
});

test('product listing only shows active products', function () {
    $category = Category::factory()->create();
    $active = Product::factory()->for($category)->create(['is_active' => true]);
    $inactive = Product::factory()->for($category)->create(['is_active' => false]);

    $response = $this->get(route('products.index'));

    $response->assertSee($active->name);
    $response->assertDontSee($inactive->name);
});

test('product detail page shows product information', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true]);

    $response = $this->get(route('products.show', $product));

    $response->assertStatus(200);
    $response->assertSee($product->name);
});

test('inactive product detail page returns 404', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => false]);

    $response = $this->get(route('products.show', $product));

    $response->assertStatus(404);
});

test('home page cache invalidates when a product is updated', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['is_active' => true]);

    $this->get(route('home'));

    expect(Cache::has('storefront:home:new-arrivals'))->toBeTrue();

    $product->update(['name' => 'Updated Name']);

    expect(Cache::has('storefront:home:new-arrivals'))->toBeFalse();
});

test('category tree cache invalidates when a category is updated', function () {
    $category = Category::factory()->create();

    $this->get(route('products.index'));

    expect(Cache::has('storefront:categories:tree'))->toBeTrue();

    $category->update(['name' => 'Updated Category']);

    expect(Cache::has('storefront:categories:tree'))->toBeFalse();
});
