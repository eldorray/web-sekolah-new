<?php

declare(strict_types=1);

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsRequest;
use App\Models\News;
use App\Services\FileStore;
use App\Services\HtmlSanitizer;
use App\Support\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(Request $request): Response
    {
        return inertia('guru/News/Index', [
            'news' => $this->ownedBy($request)->latest('published_at')->get()
                ->map(fn (News $item): array => $this->row($item))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return inertia('guru/News/Form', [
            'categories' => News::CATEGORIES,
        ]);
    }

    public function store(NewsRequest $request, HtmlSanitizer $sanitizer, FileStore $files): RedirectResponse
    {
        News::create([
            ...$this->payload($request, $sanitizer),
            'user_id' => (int) $request->user()->id,
            'slug' => $this->uniqueSlug($request->string('title')->value()),
            'image' => $request->hasFile('image')
                ? $files->putPublic($request->file('image'), 'news')
                : null,
        ]);

        Toast::success('Berita tersimpan.');

        return to_route('guru.news.index');
    }

    public function edit(News $news): Response
    {
        Gate::authorize('update', $news);

        return inertia('guru/News/Form', [
            'news' => [
                ...$this->row($news),
                'excerpt' => $news->excerpt,
                'content' => $news->content,
                'image' => $news->imageUrl(),
            ],
            'categories' => News::CATEGORIES,
        ]);
    }

    public function update(NewsRequest $request, News $news, HtmlSanitizer $sanitizer, FileStore $files): RedirectResponse
    {
        Gate::authorize('update', $news);

        $news->update([
            ...$this->payload($request, $sanitizer),
            'slug' => $news->title === $request->string('title')->value()
                ? $news->slug
                : $this->uniqueSlug($request->string('title')->value(), $news->id),
            ...($request->hasFile('image')
                ? ['image' => $files->putPublic($request->file('image'), 'news', $news->image)]
                : []),
        ]);

        Toast::success('Perubahan tersimpan.');

        return to_route('guru.news.index');
    }

    public function destroy(News $news): RedirectResponse
    {
        Gate::authorize('delete', $news);

        $news->delete();

        Toast::success('Berita dipindahkan ke tempat sampah.');

        return back();
    }

    /** @return Builder<News> */
    private function ownedBy(Request $request): Builder
    {
        return News::query()->where('user_id', $request->user()->id);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(NewsRequest $request, HtmlSanitizer $sanitizer): array
    {
        return [
            'title' => $request->string('title')->value(),
            'category' => $request->string('category')->value(),
            'excerpt' => $request->string('excerpt')->value(),
            'content' => $sanitizer->clean($request->string('content')->value()),
            'published_at' => $request->date('published_at'),
            'is_published' => $request->boolean('is_published'),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (News::withTrashed()->where('slug', $slug)->whereKeyNot($ignoreId ?? 0)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function row(News $item): array
    {
        return [
            'slug' => $item->slug,
            'title' => $item->title,
            'category' => $item->category,
            'published_at' => $item->published_at?->toDateString(),
            'is_published' => $item->is_published,
        ];
    }
}
