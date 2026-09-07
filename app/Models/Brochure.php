<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $preview_image
 * @property string|null $file
 * @property int $order
 * @property bool $is_active
 * @property int|null $images_count
 */
class Brochure extends Model
{
    protected $fillable = ['title', 'subtitle', 'preview_image', 'file', 'order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /** @return HasMany<BrochureImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(BrochureImage::class)->orderBy('order');
    }

    public function previewUrl(): ?string
    {
        return self::resolveUrl($this->preview_image);
    }

    public function fileUrl(): ?string
    {
        return self::resolveUrl($this->file);
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
