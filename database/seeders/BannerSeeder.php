<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
}
