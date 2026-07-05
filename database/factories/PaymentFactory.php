<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'gateway' => 'sslcommerz',
            'transaction_id' => strtoupper($this->faker->bothify('TXN-########')),
            'val_id' => null,
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => 'initiated',
            'gateway_response' => null,
            'paid_at' => null,
        ];
    }

    /**
     * Indicate that the payment was successfully paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'val_id' => strtoupper($this->faker->bothify('VAL-########')),
            'gateway_response' => ['status' => 'VALID', 'tran_id' => $attributes['transaction_id'] ?? null],
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'gateway_response' => ['status' => 'FAILED', 'tran_id' => $attributes['transaction_id'] ?? null],
        ]);
    }

    /**
     * Indicate that the payment was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'gateway_response' => ['status' => 'CANCELLED', 'tran_id' => $attributes['transaction_id'] ?? null],
        ]);
    }
}
