<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Seeds a complete, realistic demo store: settings, users, categories,
 * products, banners, and a history of orders/payments.
 *
 * Run with: php artisan db:seed --class=Database\\Seeders\\DemoSeeder
 */
class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedSettings();
        $admin = $this->seedAdmin();
        $customers = $this->seedCustomers();
        $categories = $this->seedCategories();
        $products = $this->seedProducts($categories);
        $this->seedBanners();
        $this->seedOrders($customers, $products);

        $this->command?->info('Demo data seeded: '.
            "1 admin, {$customers->count()} customers, {$categories->count()} categories, ".
            "{$products->count()} products, orders with payments & status history.");
        $this->command?->line('Admin login: admin@example.com / password');
        $this->command?->line('Customer login: customer@example.com / password');
    }

    /**
     * Seed application-wide settings (app name).
     */
    private function seedSettings(): void
    {
        Setting::set('app_name', 'MegaMart');
    }

    /**
     * Seed the admin user.
     */
    private function seedAdmin(): User
    {
        return User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
    }

    /**
     * Seed customer users, each with a profile and one or two addresses.
     *
     * @return Collection<int, User>
     */
    private function seedCustomers(): Collection
    {
        $mainCustomer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
        ]);

        $otherCustomers = User::factory(9)->create();

        $customers = collect([$mainCustomer])->merge($otherCustomers);

        $customers->each(function (User $customer): void {
            Profile::factory()->for($customer)->create();

            Address::factory()->for($customer)->default()->create();

            if (fake()->boolean(40)) {
                Address::factory()->for($customer)->create();
            }
        });

        return $customers;
    }

    /**
     * Seed top-level categories with a couple of children each.
     *
     * @return Collection<int, Category>
     */
    private function seedCategories(): Collection
    {
        $tree = [
            'Electronics' => ['Laptops', 'Smartphones', 'Accessories'],
            'Fashion' => ["Men's Clothing", "Women's Clothing", 'Footwear'],
            'Home & Kitchen' => ['Furniture', 'Cookware'],
            'Beauty' => ['Skincare', 'Makeup'],
        ];

        $allCategories = collect();

        foreach ($tree as $parentName => $children) {
            $parent = Category::factory()->create(['name' => $parentName]);
            $allCategories->push($parent);

            foreach ($children as $childName) {
                $allCategories->push(Category::factory()->childOf($parent)->create(['name' => $childName]));
            }
        }

        return $allCategories;
    }

    /**
     * Seed products spread across the leaf categories, with images and a
     * realistic mix of on-sale, low-stock, and out-of-stock items.
     *
     * @param  Collection<int, Category>  $categories
     * @return Collection<int, Product>
     */
    private function seedProducts(Collection $categories): Collection
    {
        $leafCategories = $categories->filter(fn (Category $category) => $category->parent_id !== null);

        $products = collect();

        foreach ($leafCategories as $category) {
            $products = $products->merge(
                Product::factory(6)
                    ->for($category)
                    ->has(ProductImage::factory()->count(2), 'images')
                    ->create()
            );
        }

        // A handful of on-sale products for the homepage "Featured" rail.
        $products->random(8)->each(function (Product $product): void {
            $product->update(['sale_price' => round((float) $product->price * 0.8, 2)]);
        });

        // A few low-stock and out-of-stock products to exercise admin/storefront edge cases.
        $products->random(5)->each(fn (Product $product) => $product->update([
            'stock_qty' => $product->low_stock_threshold,
        ]));

        $products->random(3)->each(fn (Product $product) => $product->update(['stock_qty' => 0]));

        return $products;
    }

    /**
     * Seed homepage banners.
     */
    private function seedBanners(): void
    {
        $banners = [
            [
                'title' => 'Summer Collection 2026',
                'subtitle' => 'Fresh styles for the season — up to 40% off selected items.',
                'image_path' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1400&h=500&fit=crop&q=80',
                'link_url' => '/products',
                'link_text' => 'Shop Now',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'New Electronics Arrivals',
                'subtitle' => 'Latest gadgets and accessories delivered to your doorstep.',
                'image_path' => 'https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=1400&h=500&fit=crop&q=80',
                'link_url' => '/products',
                'link_text' => 'Explore',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Free Delivery Nationwide',
                'subtitle' => 'Order above ৳2,000 and enjoy free shipping across Bangladesh.',
                'image_path' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1400&h=500&fit=crop&q=80',
                'link_url' => '/products',
                'link_text' => 'Start Shopping',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }

    /**
     * Seed orders (with items, payments, and status history) for a subset
     * of customers, covering the full range of order statuses.
     *
     * @param  Collection<int, User>  $customers
     * @param  Collection<int, Product>  $products
     */
    private function seedOrders(Collection $customers, Collection $products): void
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'delivered', 'cancelled'];

        foreach ($statuses as $status) {
            $customer = $customers->random();
            $orderProducts = $products->random(fake()->numberBetween(1, 4));

            $subtotal = 0;
            $items = $orderProducts->map(function (Product $product) use (&$subtotal) {
                $qty = fake()->numberBetween(1, 3);
                $unitPrice = (float) ($product->sale_price ?? $product->price);
                $subtotal += $unitPrice * $qty;

                return ['product_id' => $product->id, 'qty' => $qty, 'unit_price' => $unitPrice];
            });

            $shipping = 60.0;
            $tax = 0.0;

            $address = $customer->addresses()->first();

            $order = Order::factory()->for($customer)->create([
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'total' => $subtotal + $shipping + $tax,
                'shipping_address' => $address ? [
                    'label' => $address->label,
                    'line1' => $address->line1,
                    'line2' => $address->line2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                ] : null,
            ]);

            foreach ($items as $item) {
                OrderItem::factory()->for($order)->create($item);
            }

            $order->markStatus('pending', 'Order placed.');

            $this->progressOrderTo($order, $status);
            $this->seedPaymentFor($order, $status);
        }
    }

    /**
     * Walk an order through the statuses leading up to its target status,
     * recording each transition in its status history.
     */
    private function progressOrderTo(Order $order, string $targetStatus): void
    {
        $timeline = ['pending', 'processing', 'shipped', 'delivered'];

        if ($targetStatus === 'cancelled') {
            $order->markStatus('cancelled', 'Order cancelled.');

            return;
        }

        $steps = array_slice($timeline, 1, array_search($targetStatus, $timeline, true));

        foreach ($steps as $step) {
            $order->markStatus($step, "Order marked as {$step}.");
        }
    }

    /**
     * Record a payment attempt matching the order's outcome.
     */
    private function seedPaymentFor(Order $order, string $status): void
    {
        $factory = Payment::factory()->for($order)->state(['amount' => $order->total]);

        match ($status) {
            'cancelled' => $factory->cancelled()->create(),
            'pending' => $factory->create(),
            default => $factory->paid()->create(),
        };
    }
}
