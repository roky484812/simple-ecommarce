<?php

namespace Database\Seeders;

use App\Models\Category;
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
        Category::factory()->childOf($fashion)->create(['name' => "Men's Clothing", 'slug' => 'mens-clothing']);
        Category::factory()->childOf($fashion)->create(['name' => "Women's Clothing", 'slug' => 'womens-clothing']);
    }
}
