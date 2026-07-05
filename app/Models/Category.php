<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'parent_id', 'is_active'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * The parent category, if any.
     *
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * The child categories.
     *
     * @return HasMany<Category>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * The child categories, eager-loaded recursively (all descendant levels).
     *
     * @return HasMany<Category>
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Get every descendant category ID (children, grandchildren, ...), regardless of depth.
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = [...$ids, ...$child->descendantIds()];
        }

        return $ids;
    }

    /**
     * Determine if this category has at least one descendant (any depth).
     */
    public function hasDescendants(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Auto-generate a unique slug from the name when one isn't provided.
     */
    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if ($category->slug) {
                return;
            }

            $base = Str::slug($category->name);
            $slug = $base;
            $suffix = 1;

            while (
                static::where('slug', $slug)
                    ->when($category->exists, fn ($query) => $query->whereKeyNot($category->getKey()))
                    ->withTrashed()
                    ->exists()
            ) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $category->slug = $slug;
        });
    }
}
