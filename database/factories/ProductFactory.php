<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 500);
        $name = ucwords(fake()->unique()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 1000000),
            'short_description' => fake()->paragraph(),
            'long_description' => fake()->paragraphs(3, true),
            'price' => $price,
            'sale_price' => null,
            'sku' => strtoupper(fake()->unique()->bothify('SKU-#####??')),
            'stock_qty' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 5,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the product is on sale.
     */
    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price' => round($attributes['price'] * 0.8, 2),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_qty' => 0,
        ]);
    }

    /**
     * Indicate that the product's stock is at or below its low-stock threshold.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_qty' => $attributes['low_stock_threshold'] ?? 5,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
