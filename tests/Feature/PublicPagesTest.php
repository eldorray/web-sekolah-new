<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ContactAdminAlertMail;
use App\Mail\PpdbAdminAlertMail;
use App\Mail\PpdbReceivedMail;
use App\Models\ContactMessage;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\PpdbRegistration;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use App\Models\VisitSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_home_lists_published_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/Home')
                ->has('programs', 3)
                ->has('news', 3)
                ->has('stats', 4));
    }

    public function test_program_detail_renders_by_slug(): void
    {
        $program = Program::query()->active()->firstOrFail();

        $this->get("/program/{$program->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/Programs/Show')
                ->where('program.title', $program->title));
    }

    public function test_inactive_program_is_not_public(): void
    {
        $program = Program::query()->where('is_active', false)->firstOrFail();

        $this->get("/program/{$program->slug}")->assertNotFound();
    }

    public function test_unpublished_news_is_not_public(): void
    {
        $draft = News::query()->where('is_published', false)->firstOrFail();

        $this->get("/berita/{$draft->slug}")->assertNotFound();
    }

    public function test_gallery_album_returns_photos(): void
    {
        $album = GalleryAlbum::query()->published()->firstOrFail();

        $this->get("/galeri/{$album->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/Gallery')
                ->has('album.photos', $album->photos()->count()));
    }

    public function test_teachers_page_hides_inactive_staff(): void
    {
        $expected = User::query()->activeTeachers()->count();

        $this->get('/tim-guru')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->has('teachers', $expected));
    }

    public function test_contact_form_stores_a_message(): void
    {
        $this->post('/kontak', [
            'name' => 'Wali Murid',
            'email' => 'wali@email.com',
            'phone' => '08123456789',
            'subject' => 'Biaya masuk',
            'message' => 'Berapa biaya masuk kelas VII?',
            'form_started_at' => now()->subMinute()->timestamp,
        ])
            ->assertRedirect()
            ->assertInertiaFlash('contact_sent', true);

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'wali@email.com',
            'is_read' => false,
        ]);
    }

    public function test_contact_form_rejects_honeypot_submissions(): void
    {
        $this->post('/kontak', [
            'name' => 'Bot',
            'email' => 'bot@email.com',
            'subject' => 'Spam',
            'message' => 'Spam',
            'website' => 'https://spam.example',
            'form_started_at' => now()->subMinute()->timestamp,
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('contact_messages', ContactMessage::query()->count());
    }

    public function test_visit_form_stores_a_request(): void
    {
        $this->post('/kunjungan', [
            'name' => 'SD Harapan',
            'email' => 'humas@sdharapan.sch.id',
            'visit_date' => now()->addWeek()->toDateString(),
            'participants' => 30,
            'purpose' => 'Pengenalan jenjang SMP',
            'form_started_at' => now()->subMinute()->timestamp,
        ])->assertRedirect();

        $this->assertDatabaseHas('visit_schedules', ['email' => 'humas@sdharapan.sch.id']);
    }

    public function test_ppdb_submission_stores_documents_privately(): void
    {
        Storage::fake('local');

        $this->post('/ppdb', [
            'full_name' => 'Calon Siswa Baru',
            'nickname' => 'Calon',
            'gender' => 'L',
            'birthplace' => 'Bandung',
            'birthdate' => now()->subYears(12)->toDateString(),
            'previous_school' => 'SD Negeri 1',
            'address' => 'Jl. Contoh No. 1',
            'father_name' => 'Bapak Contoh',
            'mother_name' => 'Ibu Contoh',
            'parent_phone' => '08123456789',
            'parent_email' => 'ortu@email.com',
            'grade_target' => 'Kelas VII',
            'kk_file' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'birth_certificate_file' => UploadedFile::fake()->image('akta.jpg'),
            'form_started_at' => now()->subMinute()->timestamp,
        ])
            ->assertRedirect()
            ->assertInertiaFlash('registration_number');

        $registration = PpdbRegistration::query()->where('full_name', 'Calon Siswa Baru')->firstOrFail();

        $this->assertMatchesRegularExpression('/^PPDB-\d{4}-\d{4}$/', $registration->registration_number);
        Storage::disk('local')->assertExists((string) $registration->kk_file);
        $this->assertStringStartsWith('ppdb/', (string) $registration->kk_file);
    }

    public function test_ppdb_submission_emails_parent_and_admin(): void
    {
        Storage::fake('local');
        Mail::fake();

        $this->post('/ppdb', [
            'full_name' => 'Anak Pendaftar',
            'gender' => 'P',
            'birthplace' => 'Bandung',
            'birthdate' => now()->subYears(12)->toDateString(),
            'previous_school' => 'SD Negeri 3',
            'address' => 'Jl. Contoh No. 3',
            'father_name' => 'Bapak',
            'mother_name' => 'Ibu',
            'parent_phone' => '08123456789',
            'parent_email' => 'ortu3@email.com',
            'grade_target' => 'Kelas VII',
            'kk_file' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'birth_certificate_file' => UploadedFile::fake()->image('akta.jpg'),
            'form_started_at' => now()->subMinute()->timestamp,
        ])->assertRedirect();

        Mail::assertSent(PpdbReceivedMail::class, fn ($mail) => $mail->hasTo('ortu3@email.com'));
        Mail::assertSent(PpdbAdminAlertMail::class);
    }

    public function test_contact_message_alerts_the_admin_inbox(): void
    {
        Mail::fake();

        $this->post('/kontak', [
            'name' => 'Penanya',
            'email' => 'penanya@email.com',
            'subject' => 'Tanya jadwal',
            'message' => 'Kapan gelombang II dibuka?',
            'form_started_at' => now()->subMinute()->timestamp,
        ])->assertRedirect();

        Mail::assertSent(ContactAdminAlertMail::class);
    }

    public function test_sitemap_lists_published_content(): void
    {
        $news = News::query()->published()->firstOrFail();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('content-type'));
        $response->assertSee(route('news.show', $news->slug), false);
    }

    public function test_page_title_uses_the_school_name(): void
    {
        Setting::set('school_name', 'SMP Contoh Judul');

        $html = $this->get('/')->assertOk()->getContent();

        // Never the framework name: that was the old VITE_APP_NAME fallback.
        $this->assertStringNotContainsString('Laravel', (string) $html);
        $this->assertStringContainsString('content="SMP Contoh Judul"', (string) $html);

        // With the Vite dev server running the head is rendered by SSR, so the
        // tag is only asserted when the template emitted one.
        if (preg_match('/<title>(.*?)<\/title>/s', (string) $html, $matches) === 1) {
            $this->assertStringContainsString('SMP Contoh Judul', $matches[1]);
        }
    }

    public function test_robots_blocks_the_panels(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /guru');
    }

    public function test_ppdb_requires_documents(): void
    {
        $before = PpdbRegistration::query()->count();

        $this->post('/ppdb', [
            'full_name' => 'Tanpa Berkas',
            'gender' => 'P',
            'birthplace' => 'Bandung',
            'birthdate' => now()->subYears(12)->toDateString(),
            'previous_school' => 'SD Negeri 2',
            'address' => 'Jl. Contoh No. 2',
            'father_name' => 'Bapak',
            'mother_name' => 'Ibu',
            'parent_phone' => '08123456789',
            'parent_email' => 'ortu2@email.com',
            'grade_target' => 'Kelas VII',
        ])->assertSessionHasErrors(['kk_file', 'birth_certificate_file']);

        $this->assertSame($before, PpdbRegistration::query()->count());
    }

    public function test_registration_numbers_do_not_collide(): void
    {
        $numbers = collect(range(1, 5))->map(fn (int $index): string => PpdbRegistration::createWithNumber([
            'full_name' => "Siswa {$index}",
            'gender' => 'L',
            'birthplace' => 'Bandung',
            'birthdate' => now()->subYears(12)->toDateString(),
            'previous_school' => 'SD Negeri',
            'address' => 'Jl. Contoh',
            'father_name' => 'Ayah',
            'mother_name' => 'Ibu',
            'parent_phone' => '0812',
            'parent_email' => "siswa{$index}@email.com",
            'grade_target' => 'Kelas VII',
        ])->registration_number);

        $this->assertCount(5, $numbers->unique());
    }

    public function test_visit_schedule_rejects_past_dates(): void
    {
        $before = VisitSchedule::query()->count();

        $this->post('/kunjungan', [
            'name' => 'Telat',
            'email' => 'telat@email.com',
            'visit_date' => now()->subDay()->toDateString(),
            'participants' => 5,
            'purpose' => 'Kunjungan',
        ])->assertSessionHasErrors('visit_date');

        $this->assertSame($before, VisitSchedule::query()->count());
    }
}
