<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * A curated pool of real Unsplash photo IDs (product/e-commerce friendly:
     * electronics, fashion, accessories). Each is a permanent CDN URL on
     * images.unsplash.com — no API key required.
     *
     * @var array<int, string>
     */
    private const UNSPLASH_PHOTO_IDS = [
        'photo-1523275335684-37898b6baf30',
        'photo-1526170375885-4d8ecf77b99f',
        'photo-1505740420928-5e560c06d30e',
        'photo-1542291026-7eec264c27ff',
        'photo-1491553895911-0055eca6402d',
        'photo-1560343090-f0409e92791a',
        'photo-1542291026-7eec264c27ff',
        'photo-1523381210434-271e8be1f52b',
        'photo-1441986300917-64674bd600d8',
        'photo-1434056886845-dac89ffe9b56',
        'photo-1517336714731-489689fd1ca8',
        'photo-1445205170230-053b83016050',
        'photo-1526178613658-3f1622045557',
        'photo-1560243563-062bfc001d68',
        'photo-1571781926291-c477ebfd024b',
    ];

    /**
     * Cache of image URLs already verified reachable during this process,
     * so we don't re-request the same URL for every seeded row.
     *
     * @var array<string, bool>
     */
    private static array $verifiedUrls = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'path' => $this->workingImageUrl(),
            'sort_order' => 0,
        ];
    }

    /**
     * Pick a random Unsplash photo URL and verify it resolves with a
     * successful HTTP response before using it. Falls back to picsum.photos
     * (also verified) if every Unsplash candidate fails.
     */
    private function workingImageUrl(): string
    {
        $candidates = collect(self::UNSPLASH_PHOTO_IDS)
            ->shuffle()
            ->map(fn (string $id) => "https://images.unsplash.com/{$id}?w=800&q=80&auto=format&fit=crop");

        foreach ($candidates as $url) {
            if ($this->urlIsReachable($url)) {
                return $url;
            }
        }

        $fallback = 'https://picsum.photos/800/600?random='.fake()->unique()->numberBetween(1, 1000000);

        if ($this->urlIsReachable($fallback)) {
            return $fallback;
        }

        // Last resort: return the fallback URL unverified rather than fail the seed.
        return $fallback;
    }

    /**
     * Determine if a URL is reachable, caching the result per-URL for this run.
     */
    private function urlIsReachable(string $url): bool
    {
        if (array_key_exists($url, self::$verifiedUrls)) {
            return self::$verifiedUrls[$url];
        }

        try {
            $reachable = Http::timeout(5)->head($url)->successful();
        } catch (\Throwable $e) {
            Log::warning("ProductImageFactory: failed to reach image URL [{$url}]: {$e->getMessage()}");
            $reachable = false;
        }

        return self::$verifiedUrls[$url] = $reachable;
    }
}
