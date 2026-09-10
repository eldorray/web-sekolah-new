<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $youtube_id
 * @property string|null $description
 * @property int $order
 * @property bool $is_active
 */
class Video extends Model
{
    protected $fillable = ['title', 'youtube_id', 'description', 'order', 'is_active'];

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

    /**
     * Pulls the video id out of any YouTube address, or accepts a bare id.
     *
     * Handles watch?v=, youtu.be/, /embed/, /shorts/, and /live/ forms.
     */
    public static function parseYoutubeId(string $input): ?string
    {
        $input = trim($input);

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input) === 1) {
            return $input;
        }

        $patterns = [
            '/[?&]v=([A-Za-z0-9_-]{11})/',
            '#youtu\.be/([A-Za-z0-9_-]{11})#',
            '#/embed/([A-Za-z0-9_-]{11})#',
            '#/shorts/([A-Za-z0-9_-]{11})#',
            '#/live/([A-Za-z0-9_-]{11})#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    /** Thumbnail served by YouTube, so nothing is stored on our disk. */
    public function thumbnailUrl(): string
    {
        return "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    /** nocookie host: no YouTube tracking cookie until the visitor plays. */
    public function embedUrl(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->youtube_id}?rel=0&autoplay=1";
    }

    public function watchUrl(): string
    {
        return "https://www.youtube.com/watch?v={$this->youtube_id}";
    }
}
