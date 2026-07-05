<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Customer users
        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
        ]);

        User::factory(3)->create();

        // Categories: a couple of parents with children
        $electronics = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
        Category::factory()->childOf($electronics)->create(['name' => 'Laptops', 'slug' => 'laptops']);
        Category::factory()->childOf($electronics)->create(['name' => 'Smartphones', 'slug' => 'smartphones']);

        $fashion = Category::factory()->create(['name' => 'Fashion', 'slug' => 'fashion']);
        $mensClothing = Category::factory()->childOf($fashion)->create(['name' => "Men's Clothing", 'slug' => 'mens-clothing']);
        $womensClothing = Category::factory()->childOf($fashion)->create(['name' => "Women's Clothing", 'slug' => 'womens-clothing']);

        // Products: ~30 demo products spread across the categories above,
        // each with a couple of placeholder images.
        $categories = [$electronics, $mensClothing, $womensClothing];

        foreach ($categories as $category) {
            Product::factory(10)
                ->for($category)
                ->has(ProductImage::factory()->count(2), 'images')
                ->create();
        }
    }
}
