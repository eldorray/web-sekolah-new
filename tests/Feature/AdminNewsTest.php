<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminNewsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_guru_cannot_open_the_admin_panel(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);

        $this->actingAs($guru)->get('/admin/news')->assertForbidden();
    }

    public function test_inactive_admin_is_blocked(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => false]);

        $this->actingAs($admin)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_store_news_with_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/news', [
            'title' => 'Lomba kaligrafi antarkelas',
            'category' => 'KEGIATAN',
            'excerpt' => 'Diikuti 96 siswa dari sembilan rombel.',
            'content' => '<p>Isi berita</p>',
            'published_at' => now()->toDateString(),
            'is_published' => '1',
            'image' => UploadedFile::fake()->image('lomba.jpg'),
        ])
            ->assertRedirect('/admin/news')
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message');

        $news = News::query()->where('title', 'Lomba kaligrafi antarkelas')->firstOrFail();

        $this->assertSame('lomba-kaligrafi-antarkelas', $news->slug);
        $this->assertTrue($news->is_published);
        Storage::disk('public')->assertExists((string) $news->image);
    }

    public function test_news_content_is_sanitized_before_saving(): void
    {
        $this->actingAs($this->admin())->post('/admin/news', [
            'title' => 'Berita dengan skrip',
            'category' => 'ARTIKEL',
            'published_at' => now()->toDateString(),
            'is_published' => '1',
            'content' => '<p onclick="steal()">Halo</p><script>alert(1)</script><a href="javascript:alert(1)">tautan</a>',
        ])->assertRedirect();

        $content = (string) News::query()->where('title', 'Berita dengan skrip')->value('content');

        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onclick', $content);
        $this->assertStringNotContainsString('javascript:', $content);
        $this->assertStringContainsString('Halo', $content);
    }

    public function test_admin_can_update_and_delete_news(): void
    {
        $admin = $this->admin();
        $news = News::create([
            'user_id' => $admin->id,
            'title' => 'Judul lama',
            'slug' => 'judul-lama',
            'category' => 'ARTIKEL',
            'published_at' => now()->toDateString(),
            'is_published' => true,
        ]);

        $this->actingAs($admin)->put("/admin/news/{$news->slug}", [
            'title' => 'Judul baru',
            'category' => 'PRESTASI',
            'excerpt' => 'Ringkasan baru',
            'content' => '<p>Isi baru</p>',
            'published_at' => now()->toDateString(),
            'is_published' => '0',
        ])->assertRedirect('/admin/news');

        $news->refresh();
        $this->assertSame('Judul baru', $news->title);
        $this->assertSame('judul-baru', $news->slug);
        $this->assertFalse($news->is_published);

        $this->actingAs($admin)->delete("/admin/news/{$news->slug}")
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berita dipindahkan ke tempat sampah.');
        $this->assertSoftDeleted('news', ['id' => $news->id]);
    }

    public function test_news_changes_are_recorded_in_the_audit_log(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/news', [
            'title' => 'Berita audit',
            'category' => 'ARTIKEL',
            'published_at' => now()->toDateString(),
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'created',
            'auditable_type' => News::class,
        ]);
    }

    public function test_validation_rejects_an_unknown_category(): void
    {
        $this->actingAs($this->admin())->post('/admin/news', [
            'title' => 'Kategori salah',
            'category' => 'RANDOM',
            'published_at' => now()->toDateString(),
            'is_published' => '1',
        ])->assertSessionHasErrors('category');
    }
}
