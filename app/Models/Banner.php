<?php

namespace App\Models;

use App\Observers\BannerObserver;
use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'subtitle',
    'image_path',
    'link_url',
    'link_text',
    'sort_order',
    'is_active',
])]
#[ObservedBy([BannerObserver::class])]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The public URL for this banner's image.
     */
    public function imageUrl(): string
    {
        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Delete the underlying image file when the model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (Banner $banner): void {
            if (Str::startsWith($banner->image_path, ['http://', 'https://'])) {
                return;
            }

            Storage::disk('public')->delete($banner->image_path);
        });
    }
}
