<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoRequest;
use App\Models\Video;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Videos/Index', [
            'videos' => Video::query()->orderBy('order')->get()
                ->map(fn (Video $video): array => $this->row($video))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/Videos/Form');
    }

    public function store(VideoRequest $request): RedirectResponse
    {
        Video::create([
            ...$this->payload($request),
            'youtube_id' => $request->youtubeId(),
        ]);

        Toast::success('Video tersimpan.');

        return to_route('admin.videos.index');
    }

    public function edit(Video $video): Response
    {
        return inertia('admin/Videos/Form', [
            'id' => (string) $video->id,
            'video' => [
                ...$this->row($video),
                'description' => $video->description,
            ],
        ]);
    }

    public function update(VideoRequest $request, Video $video): RedirectResponse
    {
        $video->update([
            ...$this->payload($request),
            'youtube_id' => $request->youtubeId(),
        ]);

        Toast::success('Perubahan tersimpan.');

        return to_route('admin.videos.index');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $video->delete();

        Toast::success('Video dihapus.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(VideoRequest $request): array
    {
        return [
            'title' => $request->string('title')->value(),
            'description' => $request->string('description')->value(),
            'order' => $request->integer('order'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Video $video): array
    {
        return [
            'id' => $video->id,
            'title' => $video->title,
            'youtube_id' => $video->youtube_id,
            'thumbnail' => $video->thumbnailUrl(),
            'watch_url' => $video->watchUrl(),
            'order' => $video->order,
            'is_active' => $video->is_active,
        ];
    }
}
