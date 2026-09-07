<?php

declare(strict_types=1);

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $mine = News::query()->where('user_id', $request->user()->id);

        return inertia('guru/Dashboard', [
            'summary' => [
                [
                    'key' => 'my-news',
                    'label' => 'Berita saya',
                    'value' => (string) $mine->clone()->count(),
                    'hint' => $mine->clone()->where('is_published', false)->count().' masih draf',
                ],
                [
                    'key' => 'published',
                    'label' => 'Sudah terbit',
                    'value' => (string) $mine->clone()->where('is_published', true)->count(),
                    'hint' => 'Tampil di situs publik',
                ],
                [
                    'key' => 'latest',
                    'label' => 'Tulisan terakhir',
                    'value' => $mine->clone()->latest('published_at')->value('published_at')?->translatedFormat('j M') ?? '—',
                    'hint' => 'Tanggal terbit terakhir',
                ],
            ],
            'news' => $mine->clone()->latest('published_at')->take(4)->get()
                ->map(fn (News $item): array => [
                    'slug' => $item->slug,
                    'title' => $item->title,
                    'category' => $item->category,
                    'published_at' => $item->published_at?->toDateString(),
                    'is_published' => $item->is_published,
                ])
                ->all(),
        ]);
    }
}
