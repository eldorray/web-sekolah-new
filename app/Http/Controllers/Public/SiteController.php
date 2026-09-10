<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use App\Models\Video;
use Inertia\Response;

class SiteController extends Controller
{
    public function home(): Response
    {
        $album = GalleryAlbum::query()->published()->with('photos')->first();

        return inertia('public/Home', [
            'albumSlug' => $album?->slug,
            'about' => Setting::get('about_body'),
            'highlights' => AdminSettingController::highlights(),
            'slides' => array_map(
                fn (array $slide): array => [
                    ...$slide,
                    'image' => AdminSettingController::publicUrl($slide['image'] ?? null),
                ],
                AdminSettingController::list('home_slides'),
            ),
            'aboutPoints' => AdminSettingController::list('home_about_points'),
            'videos' => Video::query()->active()->take(12)->get()
                ->map(fn (Video $video): array => [
                    'id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'thumbnail' => $video->thumbnailUrl(),
                    'embed_url' => $video->embedUrl(),
                ])
                ->all(),
            'quote' => Setting::many(['home_quote', 'home_quote_by']),
            'cta' => Setting::many(['ppdb_cta_eyebrow', 'ppdb_cta_title', 'ppdb_cta_text']),
            'programs' => Program::query()->active()->take(3)->get()
                ->map(fn (Program $program): array => $this->programCard($program))
                ->all(),
            'news' => News::query()->published()->with('author')->take(3)->get()
                ->map(fn (News $item): array => $this->newsCard($item))
                ->all(),
            'photos' => $album?->photos
                ->take(6)
                ->map(fn ($photo): array => [
                    'id' => $photo->id,
                    'thumbnail' => $photo->thumbnailUrl(),
                    'caption' => $photo->caption,
                ])
                ->values()
                ->all() ?? [],
            'stats' => $this->stats(),
        ]);
    }

    public function about(): Response
    {
        return inertia('public/About', [
            'profile' => Setting::many([
                'npsn', 'accreditation', 'curriculum', 'hours',
                'about_body', 'about_heading', 'about_heading_accent',
                'vision', 'vision_note', 'mission',
            ]),
            'images' => [
                'hero' => Setting::imageUrl('about_hero_image'),
                'photo' => Setting::imageUrl('about_photo'),
            ],
            'stats' => $this->stats(),
        ]);
    }

    public function programs(): Response
    {
        return inertia('public/Programs/Index', [
            'programs' => Program::query()->active()->get()
                ->map(fn (Program $program): array => $this->programCard($program))
                ->all(),
        ]);
    }

    public function program(Program $program): Response
    {
        abort_unless($program->is_active, 404);

        return inertia('public/Programs/Show', [
            'slug' => $program->slug,
            'program' => [
                ...$this->programCard($program),
                'description' => $program->description,
            ],
            'others' => Program::query()->active()->whereKeyNot($program->id)->take(4)->get()
                ->map(fn (Program $other): array => $this->programCard($other))
                ->all(),
        ]);
    }

    public function news(): Response
    {
        return inertia('public/News/Index', [
            'news' => News::query()->published()->with('author')->get()
                ->map(fn (News $item): array => $this->newsCard($item))
                ->all(),
            'categories' => ['SEMUA', ...News::CATEGORIES],
        ]);
    }

    public function newsItem(News $news): Response
    {
        abort_unless($news->is_published, 404);

        return inertia('public/News/Show', [
            'slug' => $news->slug,
            'item' => [
                ...$this->newsCard($news->load('author')),
                'content' => $news->content,
            ],
            'related' => News::query()->published()->with('author')->whereKeyNot($news->id)->take(3)->get()
                ->map(fn (News $item): array => $this->newsCard($item))
                ->all(),
        ]);
    }

    public function teachers(): Response
    {
        return inertia('public/Teachers', [
            'teachers' => User::query()->activeTeachers()->get()
                ->map(fn (User $user): array => [
                    'name' => $user->name,
                    'position' => $user->position,
                    'bio' => $user->bio,
                    'photo' => $user->photoUrl(),
                    'instagram' => $user->instagram,
                    'facebook' => $user->facebook,
                ])
                ->all(),
        ]);
    }

    public function album(GalleryAlbum $album): Response
    {
        abort_unless($album->is_published, 404);

        return inertia('public/Gallery', [
            'slug' => $album->slug,
            'album' => [
                'title' => $album->title,
                'description' => $album->description,
                'photos' => $album->photos->map(fn ($photo): array => [
                    'id' => $photo->id,
                    'image' => $photo->imageUrl(),
                    'thumbnail' => $photo->thumbnailUrl(),
                    'caption' => $photo->caption,
                ])->all(),
            ],
        ]);
    }

    /**
     * @return list<array{icon: string, value: string, label: string}>
     */
    private function stats(): array
    {
        return [
            ['icon' => 'book-open', 'value' => (string) User::query()->where('role', 'guru')->count(), 'label' => 'Guru & tenaga pendidik'],
            ['icon' => 'users', 'value' => (string) Setting::get('stat_students', '0'), 'label' => 'Siswa aktif'],
            ['icon' => 'trophy', 'value' => (string) Setting::get('stat_trophies', '0'), 'label' => 'Piala tingkat kota & provinsi'],
            ['icon' => 'calendar-days', 'value' => (string) Setting::get('stat_years', '0'), 'label' => 'Tahun melayani'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function programCard(Program $program): array
    {
        return [
            'slug' => $program->slug,
            'icon' => $program->icon,
            'title' => $program->title,
            'excerpt' => $program->short_description,
            'image' => $program->imageUrl(),
            'badge' => $program->badge,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsCard(News $item): array
    {
        return [
            'slug' => $item->slug,
            'title' => $item->title,
            'category' => $item->category,
            'excerpt' => $item->excerpt,
            'date' => $item->published_at?->translatedFormat('j F Y'),
            'author' => $item->author?->name,
            'image' => $item->imageUrl(),
        ];
    }
}
