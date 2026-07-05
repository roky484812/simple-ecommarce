<?php

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['product_id', 'path', 'sort_order'])]
class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use HasFactory;

    /**
     * The product this image belongs to.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The public URL for this image. Seed data may store a full external
     * URL (e.g. Unsplash) directly in `path`; admin-uploaded images store a
     * relative path on the `public` disk.
     */
    public function url(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return Storage::disk('public')->url($this->path);
    }

    /**
     * Delete the underlying file when the model is deleted, unless it's an
     * external URL (nothing to clean up on our local disk).
     */
    protected static function booted(): void
    {
        static::deleting(function (ProductImage $image): void {
            if (Str::startsWith($image->path, ['http://', 'https://'])) {
                return;
            }

            Storage::disk('public')->delete($image->path);
        });
    }
}
