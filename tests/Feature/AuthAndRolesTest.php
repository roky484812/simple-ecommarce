<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registering creates a user with customer role by default', function () {
    $response = $this->post('/register', [
        'name' => 'New Customer',
        'email' => 'newcustomer@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/');

    $user = User::where('email', 'newcustomer@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('customer')
        ->and($user->isAdmin())->toBeFalse();
});

test('non-admin hitting /admin gets 403', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)->get('/admin');

    $response->assertStatus(403);
});

test('admin hitting /admin gets 200', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
});

test('blocked user cannot log in', function () {
    $blockedUser = User::factory()->blocked()->create([
        'email' => 'blocked@example.com',
    ]);

    $response = $this->post('/login', [
        'email' => 'blocked@example.com',
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('admin is redirected to /admin after login', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@test.com',
    ]);

    $response = $this->post('/login', [
        'email' => 'admin@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
});

test('customer is redirected to / after login', function () {
    $customer = User::factory()->create([
        'email' => 'customer@test.com',
    ]);

    $response = $this->post('/login', [
        'email' => 'customer@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
});

test('unauthenticated user cannot access /admin', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

test('login page renders', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('register page renders', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});
