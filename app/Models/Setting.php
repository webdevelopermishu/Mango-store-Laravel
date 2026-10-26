<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'store.settings';

    /** Read a setting, or $default when unset or blank. */
    public static function read(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());

        return filled($all[$key] ?? null) ? $all[$key] : $default;
    }

    /** Write a setting. Passing null clears it so the built-in default applies again. */
    public static function write(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => filled($value) ? $value : null]);
        Cache::forget(self::CACHE_KEY);
    }
}
