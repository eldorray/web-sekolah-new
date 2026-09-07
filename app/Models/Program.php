<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $icon
 * @property string|null $badge
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $image
 * @property int $order
 * @property bool $is_active
 */
class Program extends Model
{
    protected static function booted(): void
    {
        // The footer list is cached; drop it whenever a program changes.
        static::saved(fn () => Cache::forget('nav:programs'));
        static::deleted(fn () => Cache::forget('nav:programs'));
    }

    protected $fillable = [
        'title', 'slug', 'icon', 'badge', 'short_description',
        'description', 'image', 'order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /** @param  Builder<self>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('order');
    }

    public function imageUrl(): ?string
    {
        return self::resolveUrl($this->image);
    }

    /** Seeded rows may hold an absolute URL; uploads hold a storage path. */
    private static function resolveUrl(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return str_starts_with($value, 'http') ? $value : Storage::disk('public')->url($value);
    }
}
