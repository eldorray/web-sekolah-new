<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VideoGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function youtubeAddresses(): array
    {
        return [
            'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'watch with extras' => ['https://www.youtube.com/watch?t=30&v=dQw4w9WgXcQ&feature=share', 'dQw4w9WgXcQ'],
            'short host' => ['https://youtu.be/dQw4w9WgXcQ?si=abc', 'dQw4w9WgXcQ'],
            'embed' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'live' => ['https://www.youtube.com/live/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'bare id' => ['dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'not youtube' => ['https://vimeo.com/123456', null],
            'too short' => ['https://youtu.be/abc', null],
            'empty' => ['', null],
        ];
    }

    #[DataProvider('youtubeAddresses')]
    public function test_it_reads_the_video_id_from_any_youtube_address(string $input, ?string $expected): void
    {
        $this->assertSame($expected, Video::parseYoutubeId($input));
    }

    public function test_home_lists_active_videos_in_order(): void
    {
        Video::create(['title' => 'Kedua', 'youtube_id' => 'aaaaaaaaaaa', 'order' => 2, 'is_active' => true]);
        Video::create(['title' => 'Pertama', 'youtube_id' => 'bbbbbbbbbbb', 'order' => 1, 'is_active' => true]);
        Video::create(['title' => 'Disembunyikan', 'youtube_id' => 'ccccccccccc', 'order' => 3, 'is_active' => false]);

        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('videos', 2)
            ->where('videos.0.title', 'Pertama')
            ->where('videos.1.title', 'Kedua')
            ->where('videos.0.thumbnail', 'https://i.ytimg.com/vi/bbbbbbbbbbb/hqdefault.jpg'));
    }

    public function test_the_embed_uses_the_nocookie_host(): void
    {
        $video = Video::create(['title' => 'Profil', 'youtube_id' => 'dQw4w9WgXcQ', 'order' => 1, 'is_active' => true]);

        $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/', $video->embedUrl());

        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('videos.0.embed_url', $video->embedUrl()));
    }

    public function test_admin_can_add_a_video_by_pasting_a_url(): void
    {
        $this->actingAs($this->admin())->post('/admin/videos', [
            'title' => 'Wisuda Tahfizh',
            'youtube' => 'https://youtu.be/dQw4w9WgXcQ?si=xyz',
            'description' => 'Prosesi wisuda angkatan VII.',
            'order' => '1',
            'is_active' => '1',
        ])
            ->assertRedirect('/admin/videos')
            ->assertInertiaFlash('toast.message', 'Video tersimpan.');

        $this->assertDatabaseHas('videos', [
            'title' => 'Wisuda Tahfizh',
            'youtube_id' => 'dQw4w9WgXcQ',
            'is_active' => true,
        ]);
    }

    public function test_a_broken_address_is_rejected(): void
    {
        $this->actingAs($this->admin())->post('/admin/videos', [
            'title' => 'Tautan salah',
            'youtube' => 'https://contoh.test/bukan-video',
            'order' => '1',
            'is_active' => '1',
        ])->assertSessionHasErrors('youtube');

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_admin_can_update_and_delete_a_video(): void
    {
        $admin = $this->admin();
        $video = Video::create(['title' => 'Lama', 'youtube_id' => 'dQw4w9WgXcQ', 'order' => 1, 'is_active' => true]);

        $this->actingAs($admin)->put("/admin/videos/{$video->id}", [
            'title' => 'Baru',
            'youtube' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
            'order' => '3',
            'is_active' => '0',
        ])->assertRedirect('/admin/videos');

        $video->refresh();
        $this->assertSame('Baru', $video->title);
        $this->assertSame('ScMzIvxBSi4', $video->youtube_id);
        $this->assertFalse($video->is_active);

        $this->actingAs($admin)->delete("/admin/videos/{$video->id}")->assertRedirect();
        $this->assertDatabaseMissing('videos', ['id' => $video->id]);
    }

    public function test_the_video_menu_is_admin_only(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);

        $this->get('/admin/videos')->assertRedirect(route('login'));
        $this->actingAs($guru)->get('/admin/videos')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/videos')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/Videos/Index'));
    }
}
