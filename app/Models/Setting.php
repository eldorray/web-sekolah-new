<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever(
            self::cacheKey($key),
            fn (): ?string => self::query()->where('key', $key)->value('value'),
        ) ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::cacheKey($key));
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, string|null>
     */
    public static function many(array $keys): array
    {
        return collect($keys)
            ->mapWithKeys(fn (string $key): array => [$key => self::get($key)])
            ->all();
    }

    /** Public URL for a setting that stores an uploaded file path. */
    public static function imageUrl(string $key): ?string
    {
        $path = self::get($key);

        return $path === null || $path === '' ? null : Storage::disk('public')->url($path);
    }

    private static function cacheKey(string $key): string
    {
        return "setting:{$key}";
    }
}
