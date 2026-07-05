<?php

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin can view banner listing', function () {
    Banner::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.banners.index'))
        ->assertStatus(200)
        ->assertViewHas('banners');
});

test('admin can view create banner form', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.banners.create'))
        ->assertStatus(200);
});

test('admin can create a banner with image', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.banners.store'), [
            'title' => 'Summer Sale',
            'subtitle' => 'Up to 50% off',
            'image' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
            'link_url' => '/products',
            'link_text' => 'Shop Now',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.banners.index'));

    $this->assertDatabaseHas('banners', [
        'title' => 'Summer Sale',
        'subtitle' => 'Up to 50% off',
        'link_url' => '/products',
        'link_text' => 'Shop Now',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $banner = Banner::first();
    Storage::disk('public')->assertExists($banner->image_path);
});

test('admin can update a banner', function () {
    Storage::fake('public');

    $banner = Banner::factory()->create(['image_path' => 'banners/old.jpg']);
    Storage::disk('public')->put('banners/old.jpg', 'fake-image');

    $this->actingAs($this->admin)
        ->put(route('admin.banners.update', $banner), [
            'title' => 'Updated Title',
            'subtitle' => 'Updated subtitle',
            'image' => UploadedFile::fake()->image('new-banner.jpg', 1200, 400),
            'link_url' => '/categories',
            'link_text' => 'Browse',
            'sort_order' => 2,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.banners.index'));

    $banner->refresh();
    expect($banner->title)->toBe('Updated Title');
    expect($banner->sort_order)->toBe(2);

    Storage::disk('public')->assertMissing('banners/old.jpg');
    Storage::disk('public')->assertExists($banner->image_path);
});

test('admin can update a banner without changing image', function () {
    $banner = Banner::factory()->create(['title' => 'Original']);

    $this->actingAs($this->admin)
        ->put(route('admin.banners.update', $banner), [
            'title' => 'New Title',
            'sort_order' => 5,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.banners.index'));

    $banner->refresh();
    expect($banner->title)->toBe('New Title');
});

test('admin can delete a banner', function () {
    Storage::fake('public');

    $banner = Banner::factory()->create(['image_path' => 'banners/delete-me.jpg']);
    Storage::disk('public')->put('banners/delete-me.jpg', 'fake-image');

    $this->actingAs($this->admin)
        ->delete(route('admin.banners.destroy', $banner))
        ->assertRedirect(route('admin.banners.index'));

    $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    Storage::disk('public')->assertMissing('banners/delete-me.jpg');
});

test('non-admin cannot access banner management', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('admin.banners.index'))
        ->assertStatus(403);
});

test('home page shows active banners in carousel', function () {
    $activeBanner = Banner::factory()->create(['is_active' => true, 'title' => 'Active Banner']);
    $inactiveBanner = Banner::factory()->create(['is_active' => false, 'title' => 'Hidden Banner']);

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('Active Banner');
    $response->assertDontSee('Hidden Banner');
});

test('banner cache invalidates when a banner is created or updated', function () {
    Banner::factory()->create();

    $this->get(route('home'));
    expect(Cache::has('storefront:home:banners'))->toBeTrue();

    Banner::factory()->create(['title' => 'New One']);
    expect(Cache::has('storefront:home:banners'))->toBeFalse();
});
