<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $cover_image
 * @property int $order
 * @property bool $is_published
 * @property int|null $photos_count
 * @property-read Collection<int, GalleryPhoto> $photos
 */
class GalleryAlbum extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'cover_image', 'order', 'is_published'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'order' => 'integer',
        ];
    }

    /** @return HasMany<GalleryPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class, 'album_id')->orderBy('order');
    }

    /** @param  Builder<self>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('order');
    }

    public function coverUrl(): ?string
    {
        return self::resolveUrl($this->cover_image);
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
