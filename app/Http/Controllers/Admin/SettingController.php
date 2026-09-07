<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FileStore;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Response;

class SettingController extends Controller
{
    /** @var list<string> */
    private const IDENTITY_KEYS = [
        'school_name', 'foundation_name', 'headmaster_name', 'npsn', 'address',
        'email', 'phone', 'whatsapp', 'hours', 'instagram', 'facebook',
        'youtube', 'map_embed',
    ];

    /** @var list<string> */
    private const PROFILE_KEYS = [
        'accreditation', 'curriculum', 'about_body', 'vision', 'mission',
        'vision_note', 'about_heading', 'about_heading_accent',
        'stat_students', 'stat_trophies', 'stat_years',
    ];

    /** Icons the public Icon component can render. */
    private const ICONS = [
        'book-marked', 'book-open', 'calendar-days', 'cpu', 'flask-conical',
        'graduation-cap', 'languages', 'library-big', 'microscope', 'phone',
        'sprout', 'trophy', 'users', 'volleyball',
    ];

    /** @var list<string> */
    private const IMAGE_KEYS = [
        'logo', 'favicon', 'hero_image', 'og_image',
        'about_hero_image', 'about_photo',
    ];

    public function edit(): Response
    {
        return inertia('admin/Settings', [
            'values' => Setting::many([
                ...self::IDENTITY_KEYS,
                ...self::PROFILE_KEYS,
                'home_quote', 'home_quote_by', 'ppdb_intro',
                'ppdb_cta_eyebrow', 'ppdb_cta_title', 'ppdb_cta_text',
            ]),
            'images' => collect(self::IMAGE_KEYS)
                ->mapWithKeys(fn (string $key): array => [$key => Setting::imageUrl($key)])
                ->all(),
            'highlights' => self::highlights(),
            'slides' => self::slidesWithUrls(),
            'aboutPoints' => self::list('home_about_points'),
            'ppdbSteps' => self::list('ppdb_steps'),
            'icons' => self::ICONS,
        ]);
    }

    public function updateIdentity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'foundation_name' => ['nullable', 'string', 'max:150'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:300'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'hours' => ['nullable', 'string', 'max:120'],
            'instagram' => ['nullable', 'url', 'max:200'],
            'facebook' => ['nullable', 'url', 'max:200'],
            'youtube' => ['nullable', 'url', 'max:200'],
            'map_embed' => ['nullable', 'url', 'max:500'],
        ]);

        Setting::setMany($validated);

        Toast::success('Identitas sekolah tersimpan.');

        return back();
    }

    /**
     * The four cards under the hero.
     *
     * ponytail: stored as one JSON setting instead of its own table — four
     * short rows that change once a year do not need a CRUD module.
     *
     * @return list<array{icon: string, title: string, text: string}>
     */
    public static function highlights(): array
    {
        $stored = json_decode((string) Setting::get('home_highlights', '[]'), true);

        return is_array($stored) ? array_values($stored) : [];
    }

    /**
     * Any JSON-backed list setting, decoded.
     *
     * @return list<array<string, string|null>>
     */
    public static function list(string $key): array
    {
        $stored = json_decode((string) Setting::get($key, '[]'), true);

        return is_array($stored) ? array_values($stored) : [];
    }

    public function updateHome(Request $request, FileStore $files): RedirectResponse
    {
        $validated = $request->validate([
            'highlights' => ['present', 'array', 'max:8'],
            'highlights.*.icon' => ['required', Rule::in(self::ICONS)],
            'highlights.*.title' => ['required', 'string', 'max:60'],
            'highlights.*.text' => ['required', 'string', 'max:160'],

            'slides' => ['present', 'array', 'max:5'],
            'slides.*.eyebrow' => ['required', 'string', 'max:120'],
            'slides.*.title' => ['required', 'string', 'max:60'],
            'slides.*.accent' => ['nullable', 'string', 'max:40'],
            'slides.*.title_end' => ['nullable', 'string', 'max:60'],
            'slides.*.text' => ['required', 'string', 'max:300'],
            'slides.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'slides.*.existing_image' => ['nullable', 'string', 'max:255'],

            'about_points' => ['present', 'array', 'max:4'],
            'about_points.*.icon' => ['required', Rule::in(self::ICONS)],
            'about_points.*.title' => ['required', 'string', 'max:80'],
            'about_points.*.text' => ['required', 'string', 'max:200'],

            'home_quote' => ['nullable', 'string', 'max:300'],
            'home_quote_by' => ['nullable', 'string', 'max:80'],
        ]);

        $slides = [];

        foreach ($validated['slides'] as $index => $slide) {
            $upload = $request->file("slides.{$index}.image");

            $slides[] = [
                'eyebrow' => $slide['eyebrow'],
                'title' => $slide['title'],
                'accent' => $slide['accent'] ?? '',
                'title_end' => $slide['title_end'] ?? '',
                'text' => $slide['text'],
                // Keep the stored image unless a new file came with this row.
                'image' => $upload !== null
                    ? $files->putPublic($upload, 'branding/slides')
                    : ($slide['existing_image'] ?? null),
            ];
        }

        Setting::setMany([
            'home_highlights' => $this->encode($validated['highlights']),
            'home_slides' => $this->encode($slides),
            'home_about_points' => $this->encode($validated['about_points']),
            'home_quote' => $validated['home_quote'] ?? null,
            'home_quote_by' => $validated['home_quote_by'] ?? null,
        ]);

        Toast::success('Konten beranda tersimpan.');

        return back();
    }

    public function updatePpdb(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ppdb_intro' => ['nullable', 'string', 'max:300'],
            'ppdb_steps' => ['present', 'array', 'max:8'],
            'ppdb_steps.*.title' => ['required', 'string', 'max:80'],
            'ppdb_steps.*.text' => ['required', 'string', 'max:200'],
            'ppdb_cta_eyebrow' => ['nullable', 'string', 'max:80'],
            'ppdb_cta_title' => ['nullable', 'string', 'max:160'],
            'ppdb_cta_text' => ['nullable', 'string', 'max:300'],
        ]);

        Setting::setMany([
            'ppdb_intro' => $validated['ppdb_intro'] ?? null,
            'ppdb_steps' => $this->encode($validated['ppdb_steps']),
            'ppdb_cta_eyebrow' => $validated['ppdb_cta_eyebrow'] ?? null,
            'ppdb_cta_title' => $validated['ppdb_cta_title'] ?? null,
            'ppdb_cta_text' => $validated['ppdb_cta_text'] ?? null,
        ]);

        Toast::success('Konten PPDB tersimpan.');

        return back();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function encode(array $rows): string
    {
        return (string) json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Slides with both the stored path (for the form) and a public URL.
     *
     * @return list<array<string, string|null>>
     */
    public static function slidesWithUrls(): array
    {
        return array_map(
            fn (array $slide): array => [
                ...$slide,
                'image_url' => self::publicUrl($slide['image'] ?? null),
            ],
            self::list('home_slides'),
        );
    }

    public static function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return str_starts_with($path, 'http')
            ? $path
            : Storage::disk('public')->url($path);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'accreditation' => ['nullable', 'string', 'max:60'],
            'curriculum' => ['nullable', 'string', 'max:60'],
            'about_body' => ['nullable', 'string', 'max:1500'],
            'about_heading' => ['nullable', 'string', 'max:80'],
            'about_heading_accent' => ['nullable', 'string', 'max:40'],
            'vision' => ['nullable', 'string', 'max:500'],
            'vision_note' => ['nullable', 'string', 'max:300'],
            // One mission point per line; the About page splits on newlines.
            'mission' => ['nullable', 'string', 'max:2000'],
            'stat_students' => ['nullable', 'string', 'max:12'],
            'stat_trophies' => ['nullable', 'string', 'max:12'],
            'stat_years' => ['nullable', 'string', 'max:12'],
        ]);

        Setting::setMany($validated);

        Toast::success('Profil sekolah tersimpan.');

        return back();
    }

    public function updateBranding(Request $request, FileStore $files): RedirectResponse
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png', 'max:512'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'about_hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'about_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        foreach (self::IMAGE_KEYS as $key) {
            if ($request->hasFile($key)) {
                Setting::set($key, $files->putPublic($request->file($key), 'branding', Setting::get($key)));
            }
        }

        Toast::success('Branding tersimpan.');

        return back();
    }
}
