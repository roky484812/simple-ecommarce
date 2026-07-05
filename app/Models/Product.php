<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'category_id',
    'name',
    'slug',
    'description',
    'price',
    'sale_price',
    'sku',
    'stock_qty',
    'low_stock_threshold',
    'is_active',
])]
#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_qty' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The category this product belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The product's images, ordered for display.
     *
     * @return HasMany<ProductImage>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Determine if the product currently has stock available.
     */
    public function isInStock(): bool
    {
        return $this->stock_qty > 0;
    }

    /**
     * Determine if the product's stock is at or below its low-stock threshold.
     */
    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_threshold;
    }

    /**
     * The price to display to customers (sale price when it's set and lower than price).
     */
    public function displayPrice(): string
    {
        if ($this->sale_price !== null && $this->sale_price < $this->price) {
            return $this->sale_price;
        }

        return $this->price;
    }

    /**
     * Auto-generate a unique slug from the name when one isn't provided.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->slug) {
                return;
            }

            $base = Str::slug($product->name);
            $slug = $base;
            $suffix = 1;

            while (
                static::where('slug', $slug)
                    ->when($product->exists, fn ($query) => $query->whereKeyNot($product->getKey()))
                    ->withTrashed()
                    ->exists()
            ) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $product->slug = $slug;
        });
    }
}
