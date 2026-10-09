<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];
    private const CACHE_KEY = 'premiumden.site_settings';

    public static function value(string $key, ?string $fallback = null): ?string
    {
        return static::allValues()->get($key, $fallback);
    }

    public static function allValues(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), fn () => static::query()->pluck('value', 'key'));
    }

    public static function forgetCache(): void { Cache::forget(self::CACHE_KEY); }
}
