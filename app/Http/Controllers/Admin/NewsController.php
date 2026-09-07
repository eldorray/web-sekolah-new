<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsRequest;
use App\Models\News;
use App\Services\FileStore;
use App\Services\HtmlSanitizer;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/News/Index', [
            'news' => News::query()->with('author')->latest('published_at')->get()
                ->map(fn (News $item): array => $this->row($item))
                ->all(),
            'categories' => News::CATEGORIES,
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/News/Form', [
            'categories' => News::CATEGORIES,
        ]);
    }

    public function store(NewsRequest $request, HtmlSanitizer $sanitizer, FileStore $files): RedirectResponse
    {
        $news = News::create([
            ...$this->payload($request, $sanitizer),
            'user_id' => (int) $request->user()->id,
            'slug' => $this->uniqueSlug($request->string('title')->value()),
            'image' => $request->hasFile('image')
                ? $files->putPublic($request->file('image'), 'news')
                : null,
        ]);

        Toast::success("Berita \"{$news->title}\" tersimpan.");

        return to_route('admin.news.index');
    }

    public function edit(News $news): Response
    {
        return inertia('admin/News/Form', [
            'slug' => $news->slug,
            'news' => [
                ...$this->row($news->load('author')),
                'excerpt' => $news->excerpt,
                'content' => $news->content,
                'image' => $news->imageUrl(),
            ],
            'categories' => News::CATEGORIES,
        ]);
    }

    public function update(NewsRequest $request, News $news, HtmlSanitizer $sanitizer, FileStore $files): RedirectResponse
    {
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

        return to_route('admin.news.index');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        Toast::success('Berita dipindahkan ke tempat sampah.');

        return back();
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
            'author' => $item->author?->name,
            'published_at' => $item->published_at?->toDateString(),
            'is_published' => $item->is_published,
        ];
    }
}
