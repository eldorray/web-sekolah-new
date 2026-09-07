<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brochure;
use App\Models\ContactMessage;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\News;
use App\Models\PpdbRegistration;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use App\Models\VisitSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminWritesTest extends TestCase
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

    public function test_admin_can_create_update_and_delete_a_program(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/programs', [
            'title' => 'Klub Jurnalistik',
            'icon' => 'book-open',
            'badge' => 'Baru',
            'short_description' => 'Menerbitkan buletin sekolah tiap bulan.',
            'description' => '<p>Isi program</p>',
            'order' => 7,
            'is_active' => '1',
        ])->assertRedirect('/admin/programs');

        $program = Program::query()->where('slug', 'klub-jurnalistik')->firstOrFail();

        $this->actingAs($admin)->put("/admin/programs/{$program->slug}", [
            'title' => 'Klub Jurnalistik',
            'icon' => 'book-open',
            'short_description' => 'Diubah.',
            'description' => '<p>Isi baru</p>',
            'order' => 7,
            'is_active' => '0',
        ])->assertRedirect();

        $this->assertFalse($program->refresh()->is_active);

        $this->actingAs($admin)->delete("/admin/programs/{$program->slug}")->assertRedirect();
        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
    }

    public function test_creating_a_teacher_hashes_the_password(): void
    {
        $this->actingAs($this->admin())->post('/admin/teachers', [
            'name' => 'Guru Baru, S.Pd.',
            'email' => 'guru.baru@sekolah.test',
            'position' => 'Guru Seni',
            'role' => 'guru',
            'is_active' => '1',
            'password' => 'rahasia-panjang',
        ])->assertRedirect('/admin/teachers');

        $teacher = User::query()->where('email', 'guru.baru@sekolah.test')->firstOrFail();

        $this->assertNotSame('rahasia-panjang', $teacher->password);
        $this->assertTrue(Hash::check('rahasia-panjang', $teacher->password));
    }

    public function test_duplicate_teacher_email_is_rejected(): void
    {
        $existing = $this->admin();

        $this->actingAs($existing)->post('/admin/teachers', [
            'name' => 'Kembar',
            'email' => $existing->email,
            'position' => 'Guru',
            'role' => 'guru',
            'is_active' => '1',
            'password' => 'rahasia-panjang',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_upload_and_delete_album_photos(): void
    {
        Storage::fake('public');

        $album = GalleryAlbum::query()->firstOrFail();
        $before = $album->photos()->count();

        $this->actingAs($this->admin())->post("/admin/gallery/{$album->slug}/photos", [
            'photos' => [
                UploadedFile::fake()->image('satu.jpg'),
                UploadedFile::fake()->image('dua.jpg'),
            ],
        ])->assertRedirect();

        $this->assertSame($before + 2, $album->photos()->count());

        $photo = GalleryPhoto::query()
            ->where('album_id', $album->id)
            ->orderByDesc('id')
            ->firstOrFail();
        Storage::disk('public')->assertExists((string) $photo->image);

        $this->actingAs($this->admin())->delete("/admin/gallery-photos/{$photo->id}")->assertRedirect();

        $this->assertDatabaseMissing('gallery_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing((string) $photo->image);
    }

    public function test_photo_upload_rejects_non_images(): void
    {
        $album = GalleryAlbum::query()->firstOrFail();

        $this->actingAs($this->admin())->post("/admin/gallery/{$album->slug}/photos", [
            'photos' => [UploadedFile::fake()->create('virus.exe', 10)],
        ])->assertSessionHasErrors('photos.0');

        $this->assertSame(0, GalleryPhoto::query()->where('image', 'like', '%exe%')->count());
    }

    public function test_admin_can_store_a_brochure_with_a_pdf(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/brochures', [
            'title' => 'Brosur Ekstrakurikuler',
            'subtitle' => 'Daftar klub dan jadwalnya',
            'order' => 9,
            'is_active' => '1',
            'file' => UploadedFile::fake()->create('ekskul.pdf', 200, 'application/pdf'),
        ])->assertRedirect('/admin/brochures');

        $brochure = Brochure::query()->where('title', 'Brosur Ekstrakurikuler')->firstOrFail();
        Storage::disk('public')->assertExists((string) $brochure->file);
    }

    public function test_uploaded_images_get_a_host_independent_url(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/teachers', [
            'name' => 'Guru Berfoto, S.Pd.',
            'email' => 'guru.foto@sekolah.test',
            'position' => 'Guru Seni',
            'role' => 'guru',
            'is_active' => '1',
            'password' => 'rahasia-panjang',
            'photo' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertRedirect();

        $teacher = User::query()->where('email', 'guru.foto@sekolah.test')->firstOrFail();

        // Relative, so the image resolves on whatever host serves the app.
        $this->assertStringStartsWith('/storage/teachers/', $teacher->photoUrl());
        Storage::disk('public')->assertExists((string) $teacher->photo);

        $this->get('/tim-guru')->assertInertia(fn ($page) => $page->where(
            'teachers',
            fn (Collection $teachers): bool => $teachers
                ->pluck('photo')
                ->contains($teacher->photoUrl()),
        ));
    }

    public function test_uploaded_branding_reaches_every_page(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/settings/branding', [
            'logo' => UploadedFile::fake()->image('logo.png'),
            'favicon' => UploadedFile::fake()->image('favicon.png', 96, 96),
        ])->assertRedirect();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('branding.logo', fn (?string $url) => str_starts_with((string) $url, '/storage/branding/')));

        // The favicon is rendered server-side in the root template.
        $response->assertSee('/storage/branding/', false);
    }

    public function test_settings_identity_is_saved_and_shared(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings/identity', [
            'school_name' => 'SMP Contoh Baru',
            'phone' => '(022) 111 222',
        ])->assertRedirect();

        $this->assertSame('SMP Contoh Baru', Setting::get('school_name'));

        $this->get('/')->assertInertia(
            fn ($page) => $page->where('school.school_name', 'SMP Contoh Baru'),
        );
    }

    public function test_public_copy_follows_the_saved_school_name(): void
    {
        Setting::set('school_name', 'MI Harapan Bangsa');
        Setting::set('foundation_name', 'Yayasan Harapan');
        Setting::set('about_body', 'Sekolah kecil di tepi kota.');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('school.school_name', 'MI Harapan Bangsa')
            ->where('school.foundation_name', 'Yayasan Harapan')
            ->where('about', 'Sekolah kecil di tepi kota.'));

        $this->get('/tentang-kami')->assertInertia(
            fn ($page) => $page->where('profile.about_body', 'Sekolah kecil di tepi kota.'),
        );
    }

    public function test_admin_can_edit_the_school_profile(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings/profile', [
            'accreditation' => 'B (2025)',
            'curriculum' => 'Merdeka',
            'about_body' => 'Profil baru sekolah.',
            'vision' => 'Visi baru.',
            'mission' => "Poin satu\nPoin dua",
            'stat_students' => '640',
            'stat_trophies' => '150',
            'stat_years' => '22',
        ])->assertRedirect();

        $this->get('/tentang-kami')->assertInertia(fn ($page) => $page
            ->where('profile.about_body', 'Profil baru sekolah.')
            ->where('profile.mission', "Poin satu\nPoin dua")
            ->where('stats.1.value', '640'));
    }

    public function test_admin_can_edit_the_home_highlight_cards(): void
    {
        $this->actingAs($this->admin())->post('/admin/settings/home', [
            'highlights' => [
                ['icon' => 'trophy', 'title' => 'Juara nasional', 'text' => 'Sembilan piala tahun ini.'],
                ['icon' => 'sprout', 'title' => 'Kebun sekolah', 'text' => 'Dikelola siswa tiap pekan.'],
            ],
            'slides' => [],
            'about_points' => [],
        ])->assertRedirect();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('highlights', 2)
            ->where('highlights.0.title', 'Juara nasional')
            ->where('highlights.1.icon', 'sprout'));
    }

    public function test_admin_can_edit_hero_slides_points_and_quote(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/settings/home', [
            'highlights' => [
                ['icon' => 'trophy', 'title' => 'Prestasi', 'text' => 'Piala tahun ini.'],
            ],
            'slides' => [
                [
                    'eyebrow' => 'Selamat datang di {school}',
                    'title' => 'Judul slide',
                    'accent' => 'aksen',
                    'title_end' => 'penutup',
                    'text' => 'Paragraf slide.',
                    'image' => UploadedFile::fake()->image('hero.jpg'),
                ],
            ],
            'about_points' => [
                ['icon' => 'sprout', 'title' => 'Poin satu', 'text' => 'Isi poin satu.'],
            ],
            'home_quote' => 'Kutipan baru.',
            'home_quote_by' => 'Wali murid kelas IX',
        ])->assertRedirect();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('slides', 1)
            ->where('slides.0.title', 'Judul slide')
            ->has('aboutPoints', 1)
            ->where('quote.home_quote', 'Kutipan baru.'));

        $stored = json_decode((string) Setting::get('home_slides'), true);
        Storage::disk('public')->assertExists($stored[0]['image']);
    }

    public function test_slide_keeps_its_image_when_no_new_file_is_sent(): void
    {
        Setting::set('home_slides', (string) json_encode([[
            'eyebrow' => 'Lama', 'title' => 'Lama', 'accent' => '', 'title_end' => '',
            'text' => 'Teks lama.', 'image' => 'branding/slides/lama.jpg',
        ]]));

        $this->actingAs($this->admin())->post('/admin/settings/home', [
            'highlights' => [],
            'about_points' => [],
            'slides' => [[
                'eyebrow' => 'Baru',
                'title' => 'Baru',
                'text' => 'Teks baru.',
                'existing_image' => 'branding/slides/lama.jpg',
            ]],
        ])->assertRedirect();

        $stored = json_decode((string) Setting::get('home_slides'), true);

        $this->assertSame('branding/slides/lama.jpg', $stored[0]['image']);
        $this->assertSame('Baru', $stored[0]['title']);
    }

    public function test_admin_can_edit_ppdb_steps_and_the_home_cta(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings/ppdb', [
            'ppdb_intro' => 'Prosesnya sepuluh hari kerja.',
            'ppdb_steps' => [
                ['title' => 'Isi formulir', 'text' => 'Lengkapi data.'],
                ['title' => 'Wawancara', 'text' => 'Bersama orang tua.'],
            ],
            'ppdb_cta_eyebrow' => 'PPDB 2028/2029',
            'ppdb_cta_title' => 'Kuota 120 siswa.',
            'ppdb_cta_text' => 'Daftar sebelum Maret.',
        ])->assertRedirect();

        $this->get('/ppdb')->assertInertia(fn ($page) => $page
            ->has('steps', 2)
            ->where('steps.1.title', 'Wawancara')
            ->where('intro', 'Prosesnya sepuluh hari kerja.'));

        $this->get('/')->assertInertia(
            fn ($page) => $page->where('cta.ppdb_cta_title', 'Kuota 120 siswa.'),
        );
    }

    public function test_hero_slides_are_capped_and_validated(): void
    {
        $this->actingAs($this->admin())->post('/admin/settings/home', [
            'highlights' => [],
            'about_points' => [],
            'slides' => [['eyebrow' => 'Tanpa judul', 'text' => '']],
        ])->assertSessionHasErrors(['slides.0.title', 'slides.0.text']);
    }

    public function test_highlight_cards_reject_an_unknown_icon(): void
    {
        $before = Setting::get('home_highlights');

        $this->actingAs($this->admin())->post('/admin/settings/home', [
            'highlights' => [
                ['icon' => 'bukan-ikon', 'title' => 'Judul', 'text' => 'Teks'],
            ],
            'slides' => [],
            'about_points' => [],
        ])->assertSessionHasErrors('highlights.0.icon');

        $this->assertSame($before, Setting::get('home_highlights'));
    }

    public function test_clearing_the_cards_falls_back_to_built_in_copy(): void
    {
        $this->actingAs($this->admin())->post('/admin/settings/home', [
            'highlights' => [],
            'slides' => [],
            'about_points' => [],
        ])->assertRedirect();

        $this->get('/')->assertInertia(fn ($page) => $page->has('highlights', 0));
    }

    public function test_about_page_copy_and_images_come_from_settings(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->put('/admin/settings/profile', [
            'about_heading' => 'Berdiri 1998, tumbuh bersama',
            'about_heading_accent' => 'warga kampung',
            'about_body' => 'Profil sekolah kami.',
            'vision' => 'Visi sekolah.',
            'vision_note' => 'Catatan di bawah visi.',
            'mission' => "Poin satu\nPoin dua\nPoin tiga",
        ])->assertRedirect();

        $this->actingAs($this->admin())->post('/admin/settings/branding', [
            'about_hero_image' => UploadedFile::fake()->image('banner.jpg'),
            'about_photo' => UploadedFile::fake()->image('gedung.jpg'),
        ])->assertRedirect();

        $this->get('/tentang-kami')->assertInertia(fn ($page) => $page
            ->where('profile.about_heading', 'Berdiri 1998, tumbuh bersama')
            ->where('profile.about_heading_accent', 'warga kampung')
            ->where('profile.vision_note', 'Catatan di bawah visi.')
            ->where('images.hero', fn (?string $url) => str_starts_with((string) $url, '/storage/branding/'))
            ->where('images.photo', fn (?string $url) => str_starts_with((string) $url, '/storage/branding/')));
    }

    public function test_admin_can_mark_a_message_read_and_delete_it(): void
    {
        $message = ContactMessage::query()->firstOrFail();

        $this->actingAs($this->admin())->put("/admin/contacts/{$message->id}/read")->assertRedirect();
        $this->assertTrue($message->refresh()->is_read);

        $this->actingAs($this->admin())->delete("/admin/contacts/{$message->id}")->assertRedirect();
        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    public function test_admin_can_change_a_visit_status(): void
    {
        $visit = VisitSchedule::query()->firstOrFail();

        $this->actingAs($this->admin())->put("/admin/visits/{$visit->id}", ['status' => 'approved'])
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Status kunjungan diperbarui.');

        $this->assertSame('approved', $visit->refresh()->status);
    }

    public function test_visit_status_rejects_unknown_values(): void
    {
        $visit = VisitSchedule::query()->firstOrFail();

        $this->actingAs($this->admin())->put("/admin/visits/{$visit->id}", ['status' => 'entah'])
            ->assertSessionHasErrors('status');
    }

    public function test_admin_can_update_ppdb_status_with_notes(): void
    {
        $registration = PpdbRegistration::query()->where('status', 'pending')->firstOrFail();

        $this->actingAs($this->admin())->put("/admin/ppdb/{$registration->registration_number}", [
            'status' => 'accepted',
            'notes' => 'Berkas lengkap.',
        ])->assertRedirect();

        $registration->refresh();
        $this->assertSame('accepted', $registration->status);
        $this->assertSame('Berkas lengkap.', $registration->notes);
    }

    public function test_ppdb_export_returns_a_csv(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/ppdb/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('registration_number', $response->streamedContent());
    }

    public function test_private_documents_are_only_reachable_by_admins(): void
    {
        Storage::fake('local');

        $registration = PpdbRegistration::query()->firstOrFail();
        $registration->update(['kk_file' => 'ppdb/kk/berkas.pdf']);
        Storage::disk('local')->put('ppdb/kk/berkas.pdf', 'isi berkas');

        // Guests first: actingAs persists for the rest of the test.
        $this->get("/admin/ppdb/{$registration->registration_number}/document/kk")
            ->assertRedirect(route('login'));

        $guru = User::query()->where('role', 'guru')->firstOrFail();

        $this->actingAs($guru)
            ->get("/admin/ppdb/{$registration->registration_number}/document/kk")
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get("/admin/ppdb/{$registration->registration_number}/document/kk")
            ->assertOk();
    }

    public function test_guru_can_publish_their_own_article(): void
    {
        $guru = User::query()->where('role', 'guru')->firstOrFail();

        $this->actingAs($guru)->post('/guru/news', [
            'title' => 'Catatan kelas VII',
            'category' => 'ARTIKEL',
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'published_at' => now()->toDateString(),
            'is_published' => '1',
        ])->assertRedirect('/guru/news');

        $this->assertDatabaseHas('news', [
            'slug' => 'catatan-kelas-vii',
            'user_id' => $guru->id,
        ]);
    }

    public function test_guru_cannot_update_another_authors_article(): void
    {
        $guru = User::query()->where('role', 'guru')->firstOrFail();
        $foreign = News::query()->firstOrFail();

        $this->actingAs($guru)->put("/guru/news/{$foreign->slug}", [
            'title' => 'Dibajak',
            'category' => 'ARTIKEL',
            'published_at' => now()->toDateString(),
            'is_published' => '1',
        ])->assertForbidden();

        $this->assertNotSame('Dibajak', $foreign->refresh()->title);
    }

    public function test_guru_can_update_their_profile(): void
    {
        $guru = User::query()->where('role', 'guru')->firstOrFail();

        $this->actingAs($guru)->put('/guru/profile', [
            'name' => 'Nama Baru, S.Pd.',
            'position' => 'Guru Bahasa',
            'bio' => 'Bio baru',
        ])->assertRedirect();

        $guru->refresh();
        $this->assertSame('Nama Baru, S.Pd.', $guru->name);
        $this->assertSame('Guru Bahasa', $guru->position);
    }
}
