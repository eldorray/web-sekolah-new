<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $album_id
 * @property string $image
 * @property string|null $thumbnail
 * @property string|null $caption
 * @property int $order
 */
class GalleryPhoto extends Model
{
    protected $fillable = ['album_id', 'image', 'thumbnail', 'caption', 'order'];

    protected function casts(): array
    {
        return ['order' => 'integer'];
    }

    /** @return BelongsTo<GalleryAlbum, $this> */
    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'album_id');
    }

    public function imageUrl(): string
    {
        return (string) self::resolveUrl($this->image);
    }

    public function thumbnailUrl(): string
    {
        return (string) self::resolveUrl($this->thumbnail ?? $this->image);
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
