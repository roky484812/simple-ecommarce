<?php

use App\Jobs\RestockNotification;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view the products list', function () {
    Product::factory()->count(3)->create();

    $response = $this->actingAs(admin())->get(route('admin.products.index'));

    $response->assertStatus(200);
});

test('non-admin cannot access product management', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->get(route('admin.products.index'));

    $response->assertStatus(403);
});

test('admin can view the create product form', function () {
    $response = $this->actingAs(admin())->get(route('admin.products.create'));

    $response->assertStatus(200);
    $response->assertSee('richTextEditor', false);
});

test('admin can view the edit product form with existing images', function () {
    $product = Product::factory()->create();
    $product->images()->create(['path' => 'products/a.jpg', 'sort_order' => 0]);

    $response = $this->actingAs(admin())->get(route('admin.products.edit', $product));

    $response->assertStatus(200);
    $response->assertSee('Main');
});

test('admin can filter products by category', function () {
    $electronics = Category::factory()->create();
    $fashion = Category::factory()->create();

    Product::factory()->create(['category_id' => $electronics->id, 'name' => 'Laptop']);
    Product::factory()->create(['category_id' => $fashion->id, 'name' => 'T-Shirt']);

    Livewire::actingAs(admin())
        ->test('admin.product-filter')
        ->set('categoryId', (string) $electronics->id)
        ->assertSee('Laptop')
        ->assertDontSee('T-Shirt');
});

test('admin can filter products by stock status', function () {
    Product::factory()->create(['name' => 'In Stock Item', 'stock_qty' => 50, 'low_stock_threshold' => 5]);
    Product::factory()->create(['name' => 'Low Stock Item', 'stock_qty' => 3, 'low_stock_threshold' => 5]);
    Product::factory()->create(['name' => 'Out Of Stock Item', 'stock_qty' => 0, 'low_stock_threshold' => 5]);

    Livewire::actingAs(admin())
        ->test('admin.product-filter')
        ->set('stockStatus', 'out_of_stock')
        ->assertSee('Out Of Stock Item')
        ->assertDontSee('In Stock Item')
        ->assertDontSee('Low Stock Item');

    Livewire::actingAs(admin())
        ->test('admin.product-filter')
        ->set('stockStatus', 'low_stock')
        ->assertSee('Low Stock Item')
        ->assertDontSee('In Stock Item')
        ->assertDontSee('Out Of Stock Item');
});

test('admin can filter products by active status', function () {
    Product::factory()->create(['name' => 'Active Item', 'is_active' => true]);
    Product::factory()->create(['name' => 'Inactive Item', 'is_active' => false]);

    Livewire::actingAs(admin())
        ->test('admin.product-filter')
        ->set('isActive', '0')
        ->assertSee('Inactive Item')
        ->assertDontSee('Active Item');
});

test('category filter select can be reset back to all categories', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);
    Product::factory()->create(['category_id' => $category->id]);

    // With a category selected, the placeholder option must not be disabled
    // so the browser can return to "All categories".
    $response = $this->actingAs(admin())->get(route('admin.products.index', ['category_id' => $category->id]));

    $response->assertStatus(200);
    $response->assertDontSee('<option value="" disabled', false);
});

test('admin can create a product with images', function () {
    Storage::fake('public');

    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Wireless Mouse',
        'sku' => 'SKU-MOUSE-001',
        'price' => 29.99,
        'stock_qty' => 50,
        'low_stock_threshold' => 5,
        'is_active' => true,
        'images' => [
            UploadedFile::fake()->image('mouse-1.jpg'),
            UploadedFile::fake()->image('mouse-2.jpg'),
        ],
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', [
        'name' => 'Wireless Mouse',
        'sku' => 'SKU-MOUSE-001',
    ]);

    $product = Product::where('sku', 'SKU-MOUSE-001')->first();

    expect($product->images)->toHaveCount(2);

    foreach ($product->images as $image) {
        Storage::disk('public')->assertExists($image->path);
    }
});

test('product slug is auto-generated when omitted', function () {
    $category = Category::factory()->create();

    $this->actingAs(admin())->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Mechanical Keyboard',
        'sku' => 'SKU-KEY-001',
        'price' => 89.99,
        'stock_qty' => 10,
        'low_stock_threshold' => 5,
    ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Mechanical Keyboard',
        'slug' => 'mechanical-keyboard',
    ]);
});

test('sale price must be lower than price', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Overpriced Sale Item',
        'sku' => 'SKU-BAD-001',
        'price' => 10,
        'sale_price' => 20,
        'stock_qty' => 5,
        'low_stock_threshold' => 5,
    ]);

    $response->assertSessionHasErrors('sale_price');
});

test('admin can update a product', function () {
    $product = Product::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs(admin())->put(route('admin.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => 'New Name',
        'sku' => $product->sku,
        'price' => $product->price,
        'stock_qty' => $product->stock_qty,
        'low_stock_threshold' => $product->low_stock_threshold,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.products.index'));

    expect($product->refresh()->name)->toBe('New Name');
});

test('unchecking active marks a product inactive', function () {
    $product = Product::factory()->create(['is_active' => true]);

    // Simulates the browser omitting the checkbox but still sending the
    // hidden fallback input with value "0" when unchecked.
    $response = $this->actingAs(admin())->put(route('admin.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => $product->price,
        'stock_qty' => $product->stock_qty,
        'low_stock_threshold' => $product->low_stock_threshold,
        'is_active' => '0',
    ]);

    $response->assertRedirect(route('admin.products.index'));

    expect($product->refresh()->is_active)->toBeFalse();
});

test('admin can remove an existing product image', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    $image = $product->images()->create(['path' => 'products/existing.jpg', 'sort_order' => 0]);
    Storage::disk('public')->put('products/existing.jpg', 'fake-contents');

    $response = $this->actingAs(admin())->put(route('admin.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => $product->price,
        'stock_qty' => $product->stock_qty,
        'low_stock_threshold' => $product->low_stock_threshold,
        'is_active' => true,
        'remove_images' => [$image->id],
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    Storage::disk('public')->assertMissing('products/existing.jpg');
});

test('admin can reorder product images', function () {
    $product = Product::factory()->create();
    $first = $product->images()->create(['path' => 'products/a.jpg', 'sort_order' => 0]);
    $second = $product->images()->create(['path' => 'products/b.jpg', 'sort_order' => 1]);

    $response = $this->actingAs(admin())->put(route('admin.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => $product->price,
        'stock_qty' => $product->stock_qty,
        'low_stock_threshold' => $product->low_stock_threshold,
        'is_active' => true,
        'image_order' => [$second->id, $first->id],
    ]);

    $response->assertRedirect(route('admin.products.index'));

    expect($second->refresh()->sort_order)->toBe(0);
    expect($first->refresh()->sort_order)->toBe(1);
    expect($product->images()->first()->id)->toBe($second->id);
});

test('product description accepts rich text HTML', function () {
    $category = Category::factory()->create();

    $html = '<p>Great <strong>product</strong>.</p><ul><li>Feature one</li></ul>';

    $this->actingAs(admin())->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Rich Text Product',
        'sku' => 'SKU-RICH-001',
        'price' => 19.99,
        'stock_qty' => 5,
        'low_stock_threshold' => 5,
        'description' => $html,
    ]);

    $this->assertDatabaseHas('products', [
        'sku' => 'SKU-RICH-001',
        'description' => $html,
    ]);
});

test('admin can delete a product', function () {
    $product = Product::factory()->create();

    $response = $this->actingAs(admin())->delete(route('admin.products.destroy', $product));

    $response->assertRedirect(route('admin.products.index'));

    $this->assertSoftDeleted($product);
});

test('stock service decrements stock without dispatching a job while above threshold', function () {
    Queue::fake();

    $product = Product::factory()->create(['stock_qty' => 20, 'low_stock_threshold' => 5]);

    app(StockService::class)->decrement($product, 5);

    expect($product->refresh()->stock_qty)->toBe(15);
    Queue::assertNotPushed(RestockNotification::class);
});

test('stock service dispatches a restock notification job when crossing the low-stock threshold', function () {
    Queue::fake();

    $product = Product::factory()->create(['stock_qty' => 6, 'low_stock_threshold' => 5]);

    app(StockService::class)->decrement($product, 2);

    expect($product->refresh()->stock_qty)->toBe(4);
    Queue::assertPushed(RestockNotification::class, function (RestockNotification $job) use ($product) {
        return $job->product->is($product);
    });
});

test('stock service does not re-dispatch when already below threshold', function () {
    Queue::fake();

    $product = Product::factory()->create(['stock_qty' => 3, 'low_stock_threshold' => 5]);

    app(StockService::class)->decrement($product, 1);

    expect($product->refresh()->stock_qty)->toBe(2);
    Queue::assertNotPushed(RestockNotification::class);
});

test('stock service can increment stock', function () {
    $product = Product::factory()->create(['stock_qty' => 10]);

    app(StockService::class)->increment($product, 5);

    expect($product->refresh()->stock_qty)->toBe(15);
});
