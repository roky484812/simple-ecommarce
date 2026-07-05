<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('non-admin cannot access payment history', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->get(route('admin.payments.index'));

    $response->assertStatus(403);
});

test('admin can view the payments list', function () {
    Payment::factory()->count(3)->create();

    $response = $this->actingAs(admin())->get(route('admin.payments.index'));

    $response->assertStatus(200);
});

test('admin can filter payments by status', function () {
    $paid = Payment::factory()->paid()->create();
    $failed = Payment::factory()->failed()->create();

    $response = $this->actingAs(admin())->get(route('admin.payments.index', ['status' => 'paid']));

    $response->assertSee($paid->transaction_id);
    $response->assertDontSee($failed->transaction_id);
});

test('admin can filter payments by gateway', function () {
    $sslcommerz = Payment::factory()->create(['gateway' => 'sslcommerz']);
    $other = Payment::factory()->create(['gateway' => 'other-gateway']);

    $response = $this->actingAs(admin())->get(route('admin.payments.index', ['gateway' => 'other-gateway']));

    $response->assertSee($other->transaction_id);
    $response->assertDontSee($sslcommerz->transaction_id);
});

test('admin can filter payments by date range', function () {
    $inRange = Payment::factory()->create(['created_at' => '2026-01-15']);
    $outOfRange = Payment::factory()->create(['created_at' => '2026-03-01']);

    $response = $this->actingAs(admin())->get(route('admin.payments.index', [
        'from' => '2026-01-01',
        'to' => '2026-01-31',
    ]));

    $response->assertSee($inRange->transaction_id);
    $response->assertDontSee($outOfRange->transaction_id);
});

test('admin can view a payment detail page including raw gateway response', function () {
    $payment = Payment::factory()->paid()->create();

    $response = $this->actingAs(admin())->get(route('admin.payments.show', $payment));

    $response->assertStatus(200);
    $response->assertSee($payment->transaction_id);
    $response->assertSee('VALID');
});

test('non-admin cannot view a payment detail page', function () {
    $customer = User::factory()->create();
    $payment = Payment::factory()->create();

    $response = $this->actingAs($customer)->get(route('admin.payments.show', $payment));

    $response->assertStatus(403);
});
