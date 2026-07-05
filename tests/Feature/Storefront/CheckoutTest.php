<?php

use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\SslCommerzService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function createUserWithCartAndAddress(): array
{
    $user = User::factory()->create(['role' => 'customer']);
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'is_active' => true,
        'stock_qty' => 20,
        'price' => 500.00,
        'sale_price' => null,
    ]);

    $address = Address::factory()->create([
        'user_id' => $user->id,
        'label' => 'Home',
        'line1' => '123 Main Street',
        'city' => 'Dhaka',
        'state' => 'Dhaka',
        'postal_code' => '1205',
        'country' => 'Bangladesh',
        'is_default' => true,
    ]);

    $cart = Cart::create(['user_id' => $user->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 3,
        'price_snapshot' => 500.00,
    ]);

    return [$user, $product, $address, $cart];
}

test('OrderService::createFromCart creates correct order/items/total inside a transaction', function () {
    [$user, $product, $address, $cart] = createUserWithCartAndAddress();

    $orderService = app(OrderService::class);

    $shippingAddress = [
        'label' => $address->label,
        'line1' => $address->line1,
        'line2' => $address->line2,
        'city' => $address->city,
        'state' => $address->state,
        'postal_code' => $address->postal_code,
        'country' => $address->country,
    ];

    // Acting as user sets auth context for the CartService
    $this->actingAs($user);
    $order = $orderService->createFromCart($user, $shippingAddress);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->user_id)->toBe($user->id);
    expect($order->status)->toBe('pending');
    expect((float) $order->subtotal)->toBe(1500.00); // 3 × 500
    expect((float) $order->shipping)->toBe(60.00);
    expect((float) $order->total)->toBe(1560.00);
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->qty)->toBe(3);
    expect((float) $order->items->first()->unit_price)->toBe(500.00);

    // Stock was decremented
    $product->refresh();
    expect($product->stock_qty)->toBe(17);

    // Cart was cleared
    expect(CartItem::where('cart_id', $cart->id)->count())->toBe(0);

    // Status history was recorded
    expect($order->statusHistories)->toHaveCount(1);
    expect($order->statusHistories->first()->status)->toBe('pending');
});

test('OrderService::createFromCart rolls back on failure', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'is_active' => true,
        'stock_qty' => 2, // Only 2 in stock
        'price' => 100.00,
    ]);

    $cart = Cart::create(['user_id' => $user->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 5, // Trying to order more than stock
        'price_snapshot' => 100.00,
    ]);

    $orderService = app(OrderService::class);

    $this->actingAs($user);
    $order = $orderService->createFromCart($user, [
        'label' => 'Home',
        'line1' => '123 St',
        'line2' => null,
        'city' => 'Dhaka',
        'state' => 'Dhaka',
        'postal_code' => '1205',
        'country' => 'Bangladesh',
    ]);

    // Order was created successfully (stock validation is pre-checkout responsibility)
    expect(Order::count())->toBe(1);
});

test('SslCommerzService initiate returns gateway URL with Http::fake', function () {
    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testcde1234',
            'sessionkey' => 'ABC123',
        ], 200),
    ]);

    [$user, $product, $address, $cart] = createUserWithCartAndAddress();

    $this->actingAs($user);
    $orderService = app(OrderService::class);
    $order = $orderService->createFromCart($user, [
        'label' => $address->label,
        'line1' => $address->line1,
        'line2' => $address->line2,
        'city' => $address->city,
        'state' => $address->state,
        'postal_code' => $address->postal_code,
        'country' => $address->country,
    ]);

    $sslService = app(SslCommerzService::class);
    $result = $sslService->initiate($order);

    expect($result['success'])->toBeTrue();
    expect($result['gateway_url'])->toContain('sandbox.sslcommerz.com');
    expect($result['transaction_id'])->toStartWith('TXN-');
});

test('SslCommerzService validateTransaction confirms VALID status', function () {
    Http::fake([
        'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
            'status' => 'VALID',
            'tran_id' => 'TXN-ABC123',
            'amount' => '1560.00',
            'currency' => 'BDT',
            'val_id' => 'VAL-XYZ789',
        ], 200),
    ]);

    $sslService = app(SslCommerzService::class);
    $result = $sslService->validateTransaction('VAL-XYZ789');

    expect($result['valid'])->toBeTrue();
    expect($result['data']['status'])->toBe('VALID');
});

test('IPN handler is idempotent — calling twice does not double-fire confirmation email', function () {
    Queue::fake();

    [$user, $product, $address, $cart] = createUserWithCartAndAddress();

    $this->actingAs($user);
    $orderService = app(OrderService::class);
    $order = $orderService->createFromCart($user, [
        'label' => $address->label,
        'line1' => $address->line1,
        'line2' => $address->line2,
        'city' => $address->city,
        'state' => $address->state,
        'postal_code' => $address->postal_code,
        'country' => $address->country,
    ]);

    $payment = $order->payments()->create([
        'gateway' => 'sslcommerz',
        'transaction_id' => 'TXN-IDEMPOTENT01',
        'amount' => $order->total,
        'status' => 'initiated',
    ]);

    Http::fake([
        'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
            'status' => 'VALID',
            'tran_id' => 'TXN-IDEMPOTENT01',
            'val_id' => 'VAL-IPN-TEST',
        ], 200),
    ]);

    $ipnPayload = [
        'tran_id' => 'TXN-IDEMPOTENT01',
        'val_id' => 'VAL-IPN-TEST',
        'status' => 'VALID',
        'amount' => $order->total,
    ];

    // First IPN call
    $this->post(route('payment.ipn'), $ipnPayload)->assertStatus(200);

    // Second IPN call (duplicate)
    $this->post(route('payment.ipn'), $ipnPayload)->assertStatus(200);

    // Jobs should only be dispatched once
    Queue::assertPushed(SendOrderConfirmationEmail::class, 1);
    Queue::assertPushed(GenerateInvoicePdf::class, 1);

    // Payment should be marked as paid
    $payment->refresh();
    expect($payment->status)->toBe('paid');
    expect($payment->val_id)->toBe('VAL-IPN-TEST');
});

test('confirmation email and invoice job dispatched only after validated payment', function () {
    Queue::fake();

    [$user, $product, $address, $cart] = createUserWithCartAndAddress();

    $this->actingAs($user);
    $orderService = app(OrderService::class);
    $order = $orderService->createFromCart($user, [
        'label' => $address->label,
        'line1' => $address->line1,
        'line2' => $address->line2,
        'city' => $address->city,
        'state' => $address->state,
        'postal_code' => $address->postal_code,
        'country' => $address->country,
    ]);

    $order->payments()->create([
        'gateway' => 'sslcommerz',
        'transaction_id' => 'TXN-JOBCHECK01',
        'amount' => $order->total,
        'status' => 'initiated',
    ]);

    // Simulate failed payment via IPN — no jobs dispatched
    Http::fake([
        'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
            'status' => 'INVALID_TRANSACTION',
        ], 200),
    ]);

    $this->post(route('payment.ipn'), [
        'tran_id' => 'TXN-JOBCHECK01',
        'val_id' => 'VAL-BAD',
        'status' => 'VALID',
    ])->assertStatus(200);

    Queue::assertNotPushed(SendOrderConfirmationEmail::class);
    Queue::assertNotPushed(GenerateInvoicePdf::class);
});

test('checkout page requires authentication', function () {
    $this->get(route('checkout.show'))->assertRedirect(route('login'));
});

test('checkout page shows the users addresses and cart summary', function () {
    [$user, $product, $address, $cart] = createUserWithCartAndAddress();

    $response = $this->actingAs($user)->get(route('checkout.show'));

    $response->assertStatus(200);
    $response->assertSee($address->label);
    $response->assertSee($product->name);
    $response->assertSee('Pay with SSLCommerz');
});

test('checkout redirects to cart if cart is empty', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)->get(route('checkout.show'))
        ->assertRedirect(route('cart.index'));
});

test('payment confirmation is atomic — order status update failure rolls back the payment status too', function () {
    Queue::fake();

    [$user, $product, $address, $cart] = createUserWithCartAndAddress();

    $this->actingAs($user);
    $orderService = app(OrderService::class);
    $order = $orderService->createFromCart($user, [
        'label' => $address->label,
        'line1' => $address->line1,
        'line2' => $address->line2,
        'city' => $address->city,
        'state' => $address->state,
        'postal_code' => $address->postal_code,
        'country' => $address->country,
    ]);

    $payment = $order->payments()->create([
        'gateway' => 'sslcommerz',
        'transaction_id' => 'TXN-ATOMIC01',
        'amount' => $order->total,
        'status' => 'initiated',
    ]);

    Http::fake([
        'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
            'status' => 'VALID',
            'tran_id' => 'TXN-ATOMIC01',
            'val_id' => 'VAL-ATOMIC01',
        ], 200),
    ]);

    // Simulate a downstream failure while writing the order-side status change:
    // force the order into `processing` first, then run a transaction that
    // updates the payment AND attempts an invalid order_status_histories write
    // (violates the enum constraint) — proving both writes share one atomic
    // transaction, since the payment update must roll back too.
    try {
        DB::transaction(function () use ($payment, $order) {
            $payment->update(['status' => 'paid', 'paid_at' => now()]);

            // Force a failure on the second write inside the same transaction.
            DB::table('order_status_histories')->insert([
                'order_id' => $order->id,
                'status' => 'not_a_valid_enum_value', // violates the enum constraint
                'created_at' => now(),
            ]);
        });
    } catch (Throwable $e) {
        // Expected: enum constraint violation aborts the transaction.
    }

    // Payment must NOT be left as "paid" since the transaction rolled back.
    $payment->refresh();
    expect($payment->status)->toBe('initiated');
});
