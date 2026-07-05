<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin can view settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.settings.edit'))
        ->assertStatus(200)
        ->assertSee('Settings');
});

test('guests cannot access settings page', function () {
    $this->get(route('admin.settings.edit'))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot access settings page', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});

test('admin can update app name', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), [
            'app_name' => 'My Awesome Store',
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('success');

    expect(Setting::get('app_name'))->toBe('My Awesome Store');
});

test('admin can upload a logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), [
            'app_name' => 'My Store',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('success');

    $logoPath = Setting::get('app_logo');
    expect($logoPath)->not->toBeNull();
    Storage::disk('public')->assertExists($logoPath);
});

test('admin can remove the logo', function () {
    Storage::fake('public');

    // First upload a logo
    Setting::set('app_logo', 'settings/logo.png');
    Storage::disk('public')->put('settings/logo.png', 'fake');

    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), [
            'app_name' => 'My Store',
            'remove_logo' => '1',
        ])
        ->assertRedirect(route('admin.settings.edit'));

    expect(Setting::get('app_logo'))->toBeNull();
    Storage::disk('public')->assertMissing('settings/logo.png');
});

test('app name is required', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), [
            'app_name' => '',
        ])
        ->assertSessionHasErrors('app_name');
});

test('logo must be a valid image', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), [
            'app_name' => 'My Store',
            'logo' => UploadedFile::fake()->create('document.pdf', 100),
        ])
        ->assertSessionHasErrors('logo');
});

test('admin can upload an svg logo', function () {
    Storage::fake('public');

    $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"/></svg>');

    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), [
            'app_name' => 'My Store',
            'logo' => $svg,
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('success');

    $logoPath = Setting::get('app_logo');
    expect($logoPath)->not->toBeNull();
    Storage::disk('public')->assertExists($logoPath);
});
