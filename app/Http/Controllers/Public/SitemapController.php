<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\Program;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('about'), 'priority' => '0.8'],
            ['loc' => route('programs.index'), 'priority' => '0.8'],
            ['loc' => route('news.index'), 'priority' => '0.8'],
            ['loc' => route('teachers.index'), 'priority' => '0.6'],
            ['loc' => route('contact'), 'priority' => '0.6'],
            ['loc' => route('ppdb.create'), 'priority' => '0.9'],
        ];

        foreach (Program::query()->active()->get() as $program) {
            $urls[] = [
                'loc' => route('programs.show', $program->slug),
                'lastmod' => $program->updated_at?->toAtomString(),
                'priority' => '0.6',
            ];
        }

        foreach (News::query()->published()->get() as $item) {
            $urls[] = [
                'loc' => route('news.show', $item->slug),
                'lastmod' => $item->updated_at?->toAtomString(),
                'priority' => '0.5',
            ];
        }

        foreach (GalleryAlbum::query()->published()->get() as $album) {
            $urls[] = [
                'loc' => route('gallery.album', $album->slug),
                'lastmod' => $album->updated_at?->toAtomString(),
                'priority' => '0.4',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
