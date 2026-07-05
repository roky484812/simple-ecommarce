<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('product filter component renders active products', function () {
    $category = Category::factory()->create();
    $active = Product::factory()->for($category)->create(['is_active' => true]);
    $inactive = Product::factory()->for($category)->create(['is_active' => false]);

    Livewire::test('storefront.product-filter')
        ->assertSee($active->name)
        ->assertDontSee($inactive->name);
});

test('product filter component filters by search term live', function () {
    $category = Category::factory()->create();
    $match = Product::factory()->for($category)->create(['is_active' => true, 'name' => 'Wireless Keyboard']);
    $other = Product::factory()->for($category)->create(['is_active' => true, 'name' => 'Cotton T-Shirt']);

    Livewire::test('storefront.product-filter')
        ->set('search', 'Keyboard')
        ->assertSee($match->name)
        ->assertDontSee($other->name);
});

test('product filter component filters by category live', function () {
    $categoryA = Category::factory()->create();
    $categoryB = Category::factory()->create();
    $productA = Product::factory()->for($categoryA)->create(['is_active' => true]);
    $productB = Product::factory()->for($categoryB)->create(['is_active' => true]);

    Livewire::test('storefront.product-filter')
        ->set('categorySlug', $categoryA->slug)
        ->assertSee($productA->name)
        ->assertDontSee($productB->name);
});

test('product filter component filters by price range live', function () {
    $category = Category::factory()->create();
    $cheap = Product::factory()->for($category)->create(['is_active' => true, 'price' => 10]);
    $expensive = Product::factory()->for($category)->create(['is_active' => true, 'price' => 500]);

    Livewire::test('storefront.product-filter')
        ->set('minPrice', '100')
        ->assertSee($expensive->name)
        ->assertDontSee($cheap->name);
});

test('product filter component resets filters', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->create(['is_active' => true, 'name' => 'Wireless Keyboard']);

    Livewire::test('storefront.product-filter')
        ->set('search', 'Keyboard')
        ->set('categorySlug', $category->slug)
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('categorySlug', '');
});

test('product filter component resets pagination when a filter changes', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->count(20)->create(['is_active' => true]);

    Livewire::test('storefront.product-filter')
        ->call('nextPage')
        ->assertSet('paginators.page', 2)
        ->set('search', 'x')
        ->assertSet('paginators.page', 1);
});
