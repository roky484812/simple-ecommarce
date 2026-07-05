<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('non-admin cannot access customer management', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->get(route('admin.customers.index'));

    $response->assertStatus(403);
});

test('admin can view the customers list', function () {
    User::factory()->count(3)->create();

    $response = $this->actingAs(admin())->get(route('admin.customers.index'));

    $response->assertStatus(200);
});

test('customers list only shows customers, not admins', function () {
    $customer = User::factory()->create(['name' => 'Jane Customer']);
    $otherAdmin = User::factory()->admin()->create(['name' => 'John Admin']);

    $response = $this->actingAs(admin())->get(route('admin.customers.index'));

    $response->assertSee('Jane Customer');
    $response->assertDontSee('John Admin');
});

test('admin can search customers by name or email', function () {
    $match = User::factory()->create(['name' => 'Alice Wonderland', 'email' => 'alice@example.com']);
    $noMatch = User::factory()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);

    $response = $this->actingAs(admin())->get(route('admin.customers.index', ['search' => 'alice']));

    $response->assertSee('Alice Wonderland');
    $response->assertDontSee('Bob Builder');
});

test('admin can view a customer detail page with orders and addresses', function () {
    $customer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $customer->id]);

    $response = $this->actingAs(admin())->get(route('admin.customers.show', $customer));

    $response->assertStatus(200);
    $response->assertSee($customer->email);
    $response->assertSee($order->order_number);
});

test('customer detail page shows profile information including avatar and bio', function () {
    $customer = User::factory()->create();
    $customer->profile()->create([
        'bio' => 'Loves gadgets and fast shipping.',
        'gender' => 'female',
        'date_of_birth' => '1995-04-12',
    ]);

    $response = $this->actingAs(admin())->get(route('admin.customers.show', $customer));

    $response->assertStatus(200);
    $response->assertSee('Loves gadgets and fast shipping.');
    $response->assertSee('Female');
    $response->assertSee($customer->avatarUrl(), false);
});

test('customer detail page falls back to a generated avatar when none uploaded', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs(admin())->get(route('admin.customers.show', $customer));

    $response->assertStatus(200);
    $response->assertSee('ui-avatars.com', false);
});

test('admin can block a customer', function () {
    $customer = User::factory()->create(['is_blocked' => false]);

    $response = $this->actingAs(admin())->post(route('admin.customers.toggle-block', $customer));

    $response->assertRedirect(route('admin.customers.show', $customer));
    expect($customer->refresh()->is_blocked)->toBeTrue();
});

test('admin can unblock a customer', function () {
    $customer = User::factory()->blocked()->create();

    $response = $this->actingAs(admin())->post(route('admin.customers.toggle-block', $customer));

    $response->assertRedirect(route('admin.customers.show', $customer));
    expect($customer->refresh()->is_blocked)->toBeFalse();
});

test('blocked customer cannot log in', function () {
    $customer = User::factory()->blocked()->create();

    $response = $this->post(route('login'), [
        'email' => $customer->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('non-admin cannot block a customer', function () {
    $customer = User::factory()->create();
    $anotherCustomer = User::factory()->create();

    $response = $this->actingAs($customer)->post(route('admin.customers.toggle-block', $anotherCustomer));

    $response->assertStatus(403);
});

test('admin can log in as a customer', function () {
    $admin = admin();
    $customer = User::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.customers.impersonate', $customer));

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($customer);
    expect(session('impersonator_id'))->toBe($admin->id);
});

test('admin cannot impersonate another admin', function () {
    $admin = admin();
    $otherAdmin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.customers.impersonate', $otherAdmin));

    $response->assertStatus(403);
    $this->assertAuthenticatedAs($admin);
});

test('admin cannot impersonate a blocked customer', function () {
    $admin = admin();
    $customer = User::factory()->blocked()->create();

    $response = $this->actingAs($admin)->post(route('admin.customers.impersonate', $customer));

    $response->assertRedirect(route('admin.customers.show', $customer));
    $response->assertSessionHas('error');
    $this->assertAuthenticatedAs($admin);
});

test('non-admin cannot start impersonation', function () {
    $customer = User::factory()->create();
    $anotherCustomer = User::factory()->create();

    $response = $this->actingAs($customer)->post(route('admin.customers.impersonate', $anotherCustomer));

    $response->assertStatus(403);
});

test('admin can stop impersonating and return to their own account', function () {
    $admin = admin();
    $customer = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.customers.impersonate', $customer));

    $response = $this->post(route('impersonate.stop'));

    $response->assertRedirect(route('admin.customers.index'));
    $this->assertAuthenticatedAs($admin);
    expect(session('impersonator_id'))->toBeNull();
});

test('stopping impersonation without an active session is forbidden', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->post(route('impersonate.stop'));

    $response->assertStatus(403);
});
