<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GalleryAlbumRequest;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Services\FileStore;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Response;

class GalleryController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Gallery/Index', [
            'albums' => GalleryAlbum::query()->withCount('photos')->orderBy('order')->get()
                ->map(fn (GalleryAlbum $album): array => $this->row($album))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/Gallery/Form');
    }

    public function store(GalleryAlbumRequest $request, FileStore $files): RedirectResponse
    {
        GalleryAlbum::create([
            ...$this->payload($request),
            'slug' => $this->uniqueSlug($request->string('title')->value()),
            'cover_image' => $request->hasFile('cover_image')
                ? $files->putPublic($request->file('cover_image'), 'gallery/covers')
                : null,
        ]);

        Toast::success('Album tersimpan.');

        return to_route('admin.gallery.index');
    }

    public function edit(GalleryAlbum $album): Response
    {
        $album->loadCount('photos');

        return inertia('admin/Gallery/Form', [
            'slug' => $album->slug,
            'album' => [
                ...$this->row($album),
                'description' => $album->description,
                'cover_image' => $album->coverUrl(),
            ],
        ]);
    }

    public function update(GalleryAlbumRequest $request, GalleryAlbum $album, FileStore $files): RedirectResponse
    {
        $album->update([
            ...$this->payload($request),
            'slug' => $album->title === $request->string('title')->value()
                ? $album->slug
                : $this->uniqueSlug($request->string('title')->value(), $album->id),
            ...($request->hasFile('cover_image')
                ? ['cover_image' => $files->putPublic($request->file('cover_image'), 'gallery/covers', $album->cover_image)]
                : []),
        ]);

        Toast::success('Perubahan tersimpan.');

        return to_route('admin.gallery.index');
    }

    public function destroy(GalleryAlbum $album, FileStore $files): RedirectResponse
    {
        foreach ($album->photos as $photo) {
            $files->deletePublic($photo->image);
        }

        $files->deletePublic($album->cover_image);
        $album->delete();

        Toast::success('Album dihapus.');

        return back();
    }

    public function photos(GalleryAlbum $album): Response
    {
        return inertia('admin/Gallery/Photos', [
            'slug' => $album->slug,
            'album' => [
                'title' => $album->title,
                'photos' => $album->photos->map(fn (GalleryPhoto $photo): array => [
                    'id' => $photo->id,
                    'thumbnail' => $photo->thumbnailUrl(),
                    'caption' => $photo->caption,
                    'order' => $photo->order,
                ])->all(),
            ],
        ]);
    }

    public function storePhotos(Request $request, GalleryAlbum $album, FileStore $files): RedirectResponse
    {
        $validated = $request->validate([
            'photos' => ['required', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $order = (int) $album->photos()->max('order');

        foreach ($validated['photos'] as $photo) {
            $order++;

            $album->photos()->create([
                'image' => $files->putPublic($photo, 'gallery/photos'),
                'order' => $order,
            ]);
        }

        Toast::success(count($validated['photos']).' foto terunggah.');

        return back();
    }

    public function updatePhoto(Request $request, GalleryPhoto $photo): RedirectResponse
    {
        $photo->update($request->validate([
            'caption' => ['nullable', 'string', 'max:200'],
            'order' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]));

        Toast::success('Keterangan foto tersimpan.');

        return back();
    }

    public function destroyPhoto(GalleryPhoto $photo, FileStore $files): RedirectResponse
    {
        $files->deletePublic($photo->image);
        $photo->delete();

        Toast::success('Foto dihapus.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(GalleryAlbumRequest $request): array
    {
        return [
            'title' => $request->string('title')->value(),
            'description' => $request->string('description')->value(),
            'order' => $request->integer('order'),
            'is_published' => $request->boolean('is_published'),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (GalleryAlbum::query()->where('slug', $slug)->whereKeyNot($ignoreId ?? 0)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function row(GalleryAlbum $album): array
    {
        return [
            'slug' => $album->slug,
            'title' => $album->title,
            'photos' => (int) ($album->photos_count ?? $album->photos()->count()),
            'order' => $album->order,
            'is_published' => $album->is_published,
        ];
    }
}
