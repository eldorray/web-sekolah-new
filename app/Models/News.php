<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $category
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $image
 * @property Carbon|null $published_at
 * @property bool $is_published
 * @property-read User|null $author
 */
class News extends Model
{
    use Auditable;
    use SoftDeletes;

    /** @var list<string> */
    public const CATEGORIES = ['KEGIATAN', 'PRESTASI', 'ARTIKEL', 'PENGUMUMAN'];

    protected $table = 'news';

    protected $fillable = [
        'user_id', 'title', 'slug', 'category', 'excerpt',
        'content', 'image', 'published_at', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'is_published' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @param  Builder<self>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', now())
            ->orderByDesc('published_at');
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
