<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brochure;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\PpdbRegistration;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function admin(): User
    {
        return User::query()->where('role', 'admin')->firstOrFail();
    }

    private function guru(): User
    {
        return User::query()->where('role', 'guru')->firstOrFail();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function adminPages(): array
    {
        return [
            'dashboard' => ['/admin', 'admin/Dashboard'],
            'news list' => ['/admin/news', 'admin/News/Index'],
            'news create' => ['/admin/news/create', 'admin/News/Form'],
            'programs list' => ['/admin/programs', 'admin/Programs/Index'],
            'programs create' => ['/admin/programs/create', 'admin/Programs/Form'],
            'teachers list' => ['/admin/teachers', 'admin/Teachers/Index'],
            'teachers create' => ['/admin/teachers/create', 'admin/Teachers/Form'],
            'brochures list' => ['/admin/brochures', 'admin/Brochures/Index'],
            'brochures create' => ['/admin/brochures/create', 'admin/Brochures/Form'],
            'gallery list' => ['/admin/gallery', 'admin/Gallery/Index'],
            'gallery create' => ['/admin/gallery/create', 'admin/Gallery/Form'],
            'icons' => ['/admin/icons', 'admin/Icons'],
            'contacts' => ['/admin/contacts', 'admin/Contacts'],
            'visits' => ['/admin/visits', 'admin/Visits'],
            'settings' => ['/admin/settings', 'admin/Settings'],
            'users list' => ['/admin/users', 'admin/Users/Index'],
            'users create' => ['/admin/users/create', 'admin/Users/Form'],
            'ppdb list' => ['/admin/ppdb', 'admin/Ppdb/Index'],
        ];
    }

    /**
     * @dataProvider adminPages
     */
    #[DataProvider('adminPages')]
    public function test_admin_pages_render(string $path, string $component): void
    {
        $this->actingAs($this->admin())
            ->get($path)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component($component));
    }

    #[DataProvider('adminPages')]
    public function test_guests_are_redirected(string $path): void
    {
        $this->get($path)->assertRedirect(route('login'));
    }

    #[DataProvider('adminPages')]
    public function test_guru_cannot_reach_admin_pages(string $path): void
    {
        $this->actingAs($this->guru())->get($path)->assertForbidden();
    }

    public function test_detail_pages_render_for_real_records(): void
    {
        $admin = $this->admin();
        $news = News::query()->firstOrFail();
        $program = Program::query()->firstOrFail();
        $album = GalleryAlbum::query()->firstOrFail();
        $brochure = Brochure::query()->firstOrFail();
        $registration = PpdbRegistration::query()->firstOrFail();

        $this->actingAs($admin)->get("/admin/news/{$news->slug}/edit")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/News/Form')
                ->where('news.title', $news->title));

        $this->actingAs($admin)->get("/admin/programs/{$program->slug}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/gallery/{$album->slug}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/gallery/{$album->slug}/photos")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/Gallery/Photos')
                ->has('album.photos', $album->photos()->count()));
        $this->actingAs($admin)->get("/admin/brochures/{$brochure->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/teachers/{$admin->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/users/{$admin->id}/edit")->assertOk();

        $this->actingAs($admin)->get("/admin/ppdb/{$registration->registration_number}")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/Ppdb/Show')
                ->where('registration.full_name', $registration->full_name));
    }

    public function test_dashboard_reports_real_counts(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/Dashboard')
                ->has('summary', 4)
                ->has('visitorSeries', 14)
                ->has('registrations', PpdbRegistration::query()->count()));
    }

    public function test_guru_pages_render_for_guru(): void
    {
        $guru = $this->guru();

        $this->actingAs($guru)->get('/guru')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('guru/Dashboard'));
        $this->actingAs($guru)->get('/guru/news')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('guru/News/Index'));
        $this->actingAs($guru)->get('/guru/news/create')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('guru/News/Form'));
        $this->actingAs($guru)->get('/guru/profile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('guru/Profile')
                ->where('profile.name', $guru->name));
    }

    public function test_guru_news_list_only_shows_own_articles(): void
    {
        $guru = $this->guru();

        News::create([
            'user_id' => $guru->id,
            'title' => 'Tulisan guru ini',
            'slug' => 'tulisan-guru-ini',
            'category' => 'ARTIKEL',
            'published_at' => now()->toDateString(),
            'is_published' => true,
        ]);

        $this->actingAs($guru)->get('/guru/news')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('news', 1));
    }

    public function test_guru_cannot_edit_someone_elses_article(): void
    {
        $foreign = News::query()->firstOrFail();

        $this->actingAs($this->guru())
            ->get("/guru/news/{$foreign->slug}/edit")
            ->assertForbidden();
    }
}
