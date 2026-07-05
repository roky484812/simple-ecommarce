<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("settings.{$key}", function () use ($key, $default) {
            $setting = static::find($key);

            return $setting?->value ?? $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget("settings.{$key}");
    }
}
