<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brochure;
use App\Models\ContactMessage;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\PpdbRegistration;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\VisitSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Default content for a fresh install.
 *
 * Safe to re-run: rows are matched on their natural key and updated, and
 * uploaded images are never overwritten — only text and structure are reset.
 */
class DatabaseSeeder extends Seeder
{
    private const SCHOOL = 'SMP Syekh Yusuf';

    private const FOUNDATION = 'Yayasan Pendidikan Syekh Yusuf';

    private const DOMAIN = 'smpsyekhyusuf.id';

    private const ADMIN_EMAIL = 'admin@smpsyekhyusuf.id';

    /** Local default only — change it before the site goes live. */
    private const DEFAULT_PASSWORD = 'password123';

    public function run(): void
    {
        $this->seedSettings();
        $admin = $this->seedUsers();
        $this->seedPrograms();
        $this->seedNews($admin);
        $this->seedGallery();
        $this->seedBrochures();
        $this->seedInbox();
        $this->seedRegistrations();
        $this->seedVisitorLogs();

        $this->command->info('Admin: '.self::ADMIN_EMAIL.' / '.self::DEFAULT_PASSWORD);
    }

    private function seedSettings(): void
    {
        Setting::setMany([
            // Identitas
            'school_name' => self::SCHOOL,
            'foundation_name' => self::FOUNDATION,
            'headmaster_name' => 'Drs. H. Ahmad Syukri, M.Pd.',
            'npsn' => '20254118',
            'address' => 'Jl. Syekh Yusuf No. 47, Gowa, Sulawesi Selatan 92111',
            'email' => 'info@'.self::DOMAIN,
            'phone' => '(0411) 866 214',
            'whatsapp' => '0812 4455 7788',
            'hours' => 'Senin–Jumat, 07.00–15.00 WITA',
            'instagram' => 'https://instagram.com/smpsyekhyusuf',
            'facebook' => 'https://facebook.com/smpsyekhyusuf',
            'youtube' => 'https://youtube.com/@smpsyekhyusuf',
            'map_embed' => 'https://www.openstreetmap.org/export/embed.html?bbox=119.42%2C-5.22%2C119.50%2C-5.15&layer=mapnik',

            // Profil
            'accreditation' => 'A (2025)',
            'curriculum' => 'Merdeka',
            'about_heading' => 'Berdiri 2003, tumbuh bersama',
            'about_heading_accent' => 'warga Gowa',
            'about_body' => self::SCHOOL.' dikelola '.self::FOUNDATION.'. Berawal dari empat ruang kelas, kini menampung 486 siswa dalam 18 rombel dengan penguatan tahfizh, riset sederhana, dan kepedulian lingkungan.',
            'vision' => 'Terwujudnya lulusan yang berakhlak Qur’ani, cakap berpikir, dan peduli lingkungan.',
            'vision_note' => 'Visi ini diturunkan menjadi target tahunan tiap rombel dan dievaluasi bersama komite sekolah setiap akhir semester.',
            'mission' => implode("\n", [
                'Menegakkan pembiasaan ibadah dan adab dalam kegiatan harian sekolah.',
                'Menyelenggarakan pembelajaran aktif berbasis projek dan riset sederhana.',
                'Membina hafalan Al-Qur’an minimal tiga juz secara mutqin.',
                'Menumbuhkan kepedulian lingkungan lewat program Adiwiyata.',
                'Membangun kemitraan terbuka dengan orang tua dan masyarakat.',
            ]),
            'stat_students' => '486',
            'stat_trophies' => '132',
            'stat_years' => '23',

            // Beranda
            'home_quote' => 'Anak-anak kami pulang membawa cerita tentang apa yang mereka kerjakan, bukan hanya nilai yang mereka dapat.',
            'home_quote_by' => 'Wali murid kelas VIII',

            // PPDB
            'ppdb_intro' => 'Seluruh proses memakan waktu sekitar dua pekan sejak formulir dikirim.',
            'ppdb_cta_eyebrow' => 'PPDB tahun ajaran baru',
            'ppdb_cta_title' => 'Kuota 96 siswa. Gelombang I tutup 28 Februari.',
            'ppdb_cta_text' => 'Isi formulir online, unggah kartu keluarga dan akta, lalu tunggu kabar verifikasi dalam dua hari kerja.',

        ]);

        Setting::set('home_highlights', $this->json([
            ['icon' => 'graduation-cap', 'title' => 'Beasiswa prestasi', 'text' => 'Potongan SPP hingga 100% untuk juara olimpiade dan hafizh 10 juz.'],
            ['icon' => 'users', 'title' => 'Guru pendamping', 'text' => 'Rasio 1 : 20. Setiap wali kelas melapor ke orang tua tiap bulan.'],
            ['icon' => 'library-big', 'title' => 'Perpustakaan', 'text' => '5.800 judul buku, ruang baca ber-AC, dan katalog digital.'],
            ['icon' => 'flask-conical', 'title' => 'Laboratorium', 'text' => 'Lab IPA, komputer, dan studio multimedia untuk projek P5.'],
        ]));

        Setting::set('home_about_points', $this->json([
            ['icon' => 'book-marked', 'title' => 'Kelas kecil, pendampingan personal', 'text' => 'Maksimal 24 siswa per kelas dengan laporan perkembangan bulanan ke orang tua.'],
            ['icon' => 'sprout', 'title' => 'Projek nyata, bukan hafalan saja', 'text' => 'Setiap semester siswa menuntaskan satu projek P5 yang dipakai lingkungan sekolah.'],
        ]));

        Setting::set('ppdb_steps', $this->json([
            ['title' => 'Isi formulir online', 'text' => 'Lengkapi data calon siswa dan orang tua, lalu unggah KK serta akta kelahiran.'],
            ['title' => 'Verifikasi berkas', 'text' => 'Panitia memeriksa berkas dalam 2 hari kerja dan mengabari lewat WhatsApp.'],
            ['title' => 'Tes & wawancara', 'text' => 'Tes baca Al-Qur’an, tes akademik dasar, dan wawancara bersama orang tua.'],
            ['title' => 'Pengumuman', 'text' => 'Hasil dikirim ke email pendaftar dan diumumkan di halaman pengumuman.'],
        ]));

        $this->seedSlides();
    }

    /** Slide copy is reset, but an image the admin uploaded is kept. */
    private function seedSlides(): void
    {
        $existing = json_decode((string) Setting::get('home_slides', '[]'), true);
        $existing = is_array($existing) ? array_values($existing) : [];

        $defaults = [
            [
                'eyebrow' => 'Selamat datang di {school}',
                'title' => 'Menyiapkan generasi',
                'accent' => 'berakhlak',
                'title_end' => 'dan berprestasi',
                'text' => 'Kurikulum Merdeka berpadu pembinaan tahfizh dan karakter. Kelas kecil, satu guru mendampingi 20 siswa, sehingga tiap anak terpantau.',
                'image' => 'https://picsum.photos/seed/syy-hero-1/1600/900',
            ],
            [
                'eyebrow' => 'Pendaftaran siswa baru',
                'title' => 'Gelombang I dibuka',
                'accent' => 'Januari',
                'title_end' => 'setiap tahun',
                'text' => 'Kuota 96 siswa untuk empat kelas. Seleksi berkas, tes baca Al-Qur’an, dan wawancara orang tua. Formulir bisa diisi online.',
                'image' => 'https://picsum.photos/seed/syy-hero-2/1600/900',
            ],
            [
                'eyebrow' => 'Sekolah Adiwiyata',
                'title' => 'Belajar merawat',
                'accent' => 'lingkungan',
                'title_end' => 'sejak kelas tujuh',
                'text' => 'Bank sampah, kebun sekolah, dan bengkel kompos dikelola siswa. Progres dokumen Adiwiyata terbuka untuk publik.',
                'image' => 'https://picsum.photos/seed/syy-hero-3/1600/900',
            ],
        ];

        foreach ($defaults as $index => $slide) {
            $uploaded = $existing[$index]['image'] ?? null;

            if (is_string($uploaded) && $uploaded !== '' && ! str_starts_with($uploaded, 'http')) {
                $defaults[$index]['image'] = $uploaded;
            }
        }

        Setting::set('home_slides', $this->json($defaults));
    }

    private function seedUsers(): User
    {
        $admin = User::query()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Drs. H. Ahmad Syukri, M.Pd.',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'role' => 'admin',
                'position' => 'Kepala Sekolah',
                'bio' => 'Mengajar Bahasa Indonesia sejak 2001, fokus pada literasi menulis.',
                'photo' => 'https://picsum.photos/seed/syy-g1/600/700',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $teachers = [
            ['Muh. Faiz Munawar, S.Pd.I.', 'Koordinator Tahfizh', 'Membina halaqah pagi dan program mutqin 3 juz.', true],
            ['Rina Kartika, S.Si.', 'Guru IPA · Pembina Riset', 'Mendampingi karya ilmiah siswa dan kompetisi OSN.', true],
            ['Andi Syauqi, S.Kom.', 'Guru Informatika', 'Mengelola lab komputer dan klub robotik.', true],
            ['Dewi Larasati, S.Pd.', 'Guru Matematika', 'Menyusun modul pengayaan untuk kelas olimpiade.', true],
            ['Yusuf Hamdani, Lc.', 'Guru Bahasa Arab', 'Menghidupkan hari berbahasa dan klub debat Arab.', true],
            ['Sri Handayani, S.Pd.', 'Guru BK', 'Menangani konseling siswa dan komunikasi dengan orang tua.', true],
            ['Bagas Prakoso, S.Or.', 'Guru PJOK', 'Melatih futsal dan panahan tingkat sekolah.', false],
        ];

        foreach ($teachers as $index => [$name, $position, $bio, $active]) {
            User::query()->updateOrCreate(
                ['email' => Str::slug(Str::before($name, ','), '.').'@'.self::DOMAIN],
                [
                    'name' => $name,
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'role' => 'guru',
                    'position' => $position,
                    'bio' => $bio,
                    'photo' => 'https://picsum.photos/seed/syy-g'.($index + 2).'/600/700',
                    'is_active' => $active,
                    'email_verified_at' => now(),
                ],
            );
        }

        return $admin;
    }

    private function seedPrograms(): void
    {
        $programs = [
            ['Tahfizh Al-Qur’an', 'book-marked', 'Unggulan', 'Target 3 juz mutqin selama tiga tahun, disetor harian ke guru halaqah.', true],
            ['Sains & Riset Terapan', 'microscope', 'Akademik', 'Siswa menyusun satu karya ilmiah per tahun dan mengikuti OSN tingkat kabupaten.', true],
            ['Bilingual Arab–Inggris', 'languages', 'Bahasa', 'Dua hari berbahasa per minggu, ditutup dengan sertifikasi TOEFL Junior.', true],
            ['Adiwiyata & Kewirausahaan', 'sprout', 'Karakter', 'Bank sampah, kebun sekolah, dan koperasi siswa dikelola langsung oleh siswa.', true],
            ['Robotik & Coding', 'cpu', 'Teknologi', 'Arduino, Scratch, dan Python dasar. Rutin ikut kontes robot line follower.', true],
            ['Olahraga & Seni', 'volleyball', 'Minat bakat', 'Futsal, panahan, silat, angklung, dan kaligrafi sebagai ekstrakurikuler pilihan.', false],
        ];

        foreach ($programs as $index => [$title, $icon, $badge, $excerpt, $active]) {
            Program::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'icon' => $icon,
                    'badge' => $badge,
                    'short_description' => $excerpt,
                    'description' => '<p>'.$excerpt.'</p><p>Program berjalan tiga kali sepekan di luar jam pelajaran inti. Pembina mencatat capaian tiap siswa pada kartu kendali, dan rekapnya masuk ke laporan tengah semester.</p>',
                    'image' => 'https://picsum.photos/seed/syy-prog-'.($index + 1).'/900/600',
                    'order' => $index + 1,
                    'is_active' => $active,
                ],
            );
        }
    }

    private function seedNews(User $admin): void
    {
        $items = [
            ['Tim matematika membawa pulang juara 1 OSN tingkat kabupaten', 'PRESTASI', 'Tiga siswa kelas VIII menyelesaikan babak final dengan selisih 12 poin dari pesaing terdekat.', '-4 days', true],
            ['Panen perdana kebun sekolah, hasilnya masuk kantin', 'KEGIATAN', 'Kangkung dan pakcoy dari 18 bedeng dijual ke kantin sebagai bagian projek kewirausahaan.', '-9 days', true],
            ['Jadwal PPDB gelombang I tahun ajaran baru', 'PENGUMUMAN', 'Pendaftaran dibuka 5 Januari sampai 28 Februari. Kuota 96 siswa untuk empat kelas.', '-17 days', true],
            ['Cara mendampingi anak menghafal tanpa memaksa', 'ARTIKEL', 'Catatan guru halaqah tentang ritme setoran harian yang realistis untuk siswa SMP.', '-26 days', true],
            ['Rencana pekan olahraga antarkelas', 'KEGIATAN', 'Draf jadwal dan cabang yang dipertandingkan pada pekan olahraga semester ini.', '+7 days', false],
        ];

        foreach ($items as $index => [$title, $category, $excerpt, $offset, $published]) {
            News::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'user_id' => $admin->id,
                    'title' => $title,
                    'category' => $category,
                    'excerpt' => $excerpt,
                    'content' => '<p>'.$excerpt.'</p><p>Kegiatan berlangsung di aula sekolah dan diikuti seluruh siswa kelas VII sampai IX.</p><blockquote>Yang paling berharga bukan pialanya, tetapi kebiasaan berlatih yang terbentuk sepanjang prosesnya.</blockquote>',
                    'image' => 'https://picsum.photos/seed/syy-news-'.($index + 1).'/900/600',
                    'published_at' => now()->modify($offset),
                    'is_published' => $published,
                ],
            );
        }
    }

    private function seedGallery(): void
    {
        $captions = [
            'Halaqah tahfizh pagi', 'Praktikum IPA kelas VIII', 'Panen kebun sekolah',
            'Latihan angklung', 'Kelas robotik', 'Upacara Senin', 'Lomba kaligrafi',
            'Bank sampah siswa', 'Debat Bahasa Inggris', 'Futsal antarkelas',
            'Pentas seni akhir tahun', 'Kunjungan industri',
        ];

        $album = GalleryAlbum::query()->updateOrCreate(
            ['slug' => 'kegiatan-sekolah'],
            [
                'title' => 'Kegiatan Sekolah',
                'description' => 'Cuplikan kegiatan belajar, projek P5, dan lomba sepanjang tahun ajaran berjalan.',
                'cover_image' => 'https://picsum.photos/seed/syy-photo-1/1200/800',
                'order' => 1,
                'is_published' => true,
            ],
        );

        if ($album->photos()->count() === 0) {
            foreach ($captions as $index => $caption) {
                $album->photos()->create([
                    'image' => 'https://picsum.photos/seed/syy-photo-'.($index + 1).'/1200/800',
                    'thumbnail' => 'https://picsum.photos/seed/syy-photo-'.($index + 1).'/600/400',
                    'caption' => $caption,
                    'order' => $index + 1,
                ]);
            }
        }

        GalleryAlbum::query()->updateOrCreate(
            ['slug' => 'wisuda-tahfizh'],
            [
                'title' => 'Wisuda Tahfizh Angkatan VII',
                'description' => 'Prosesi wisuda 38 siswa yang menuntaskan target hafalan.',
                'cover_image' => 'https://picsum.photos/seed/syy-wisuda/1200/800',
                'order' => 2,
                'is_published' => true,
            ],
        );
    }

    private function seedBrochures(): void
    {
        $brochures = [
            ['Brosur PPDB', 'Gelombang I & II', 1, true],
            ['Profil Sekolah', 'Fasilitas dan program unggulan', 2, true],
            ['Program Tahfizh', 'Alur setoran dan target hafalan', 3, false],
        ];

        foreach ($brochures as [$title, $subtitle, $order, $active]) {
            Brochure::query()->updateOrCreate(
                ['title' => $title],
                [
                    'subtitle' => $subtitle,
                    'preview_image' => 'https://picsum.photos/seed/syy-brochure-'.$order.'/800/1130',
                    'order' => $order,
                    'is_active' => $active,
                ],
            );
        }
    }

    private function seedInbox(): void
    {
        $messages = [
            ['Wulan Sari', 'wulan.sari@email.com', 'Biaya masuk kelas VII', 'Assalamualaikum, saya ingin menanyakan rincian biaya masuk kelas VII. Apakah ada keringanan untuk anak kedua?'],
            ['Agus Setiawan', 'agus.setiawan@email.com', 'Apakah ada bus antar-jemput?', 'Rumah kami di daerah Sungguminasa. Apakah sekolah menyediakan bus antar-jemput?'],
            ['Dewi Anggraini', 'dewi.ang@email.com', 'Jadwal tes gelombang II', 'Mohon informasi jadwal tes gelombang II beserta materi yang diujikan.'],
            ['Hendi Kurniawan', 'hendi.k@email.com', 'Permohonan kunjungan studi banding', 'Kami dari SMP Nurul Falah ingin mengajukan studi banding program Adiwiyata.'],
        ];

        foreach ($messages as $index => [$name, $email, $subject, $body]) {
            ContactMessage::query()->updateOrCreate(
                ['email' => $email, 'subject' => $subject],
                [
                    'name' => $name,
                    'phone' => '08'.random_int(1000000000, 9999999999),
                    'message' => $body,
                    'is_read' => false,
                    'created_at' => now()->subDays($index),
                ],
            );
        }

        $visits = [
            ['SMP Nurul Falah', 'humas@nurulfalah.sch.id', '+38 days', 25, 'Studi banding program Adiwiyata', 'pending'],
            ['Keluarga Bapak Hendra', 'hendra.nugraha@email.com', '+6 days', 3, 'Melihat fasilitas sebelum mendaftar', 'approved'],
            ['MI Al Ikhlas Gowa', 'mi.alikhlas@email.com', '-15 days', 40, 'Pengenalan jenjang SMP untuk kelas VI', 'done'],
        ];

        foreach ($visits as [$name, $email, $offset, $participants, $purpose, $status]) {
            VisitSchedule::query()->updateOrCreate(
                ['email' => $email, 'purpose' => $purpose],
                [
                    'name' => $name,
                    'phone' => '08'.random_int(1000000000, 9999999999),
                    'visit_date' => now()->modify($offset)->toDateString(),
                    'participants' => $participants,
                    'status' => $status,
                ],
            );
        }
    }

    private function seedRegistrations(): void
    {
        if (PpdbRegistration::query()->exists()) {
            return;
        }

        $rows = [
            ['Rafi Alfarizi Nugraha', 'Rafi', 'L', 'Gowa', 'MI Syekh Yusuf', 'Hendra Nugraha', 'Sri Wulandari', 'pending'],
            ['Aisyah Kirana Putri', 'Kirana', 'P', 'Makassar', 'SDN Sungguminasa 3', 'Bambang Sutrisno', 'Ratna Dewi', 'accepted'],
            ['Muhammad Zaki Ramadhan', 'Zaki', 'L', 'Takalar', 'MI Al Ikhlas Takalar', 'Asep Saepudin', 'Yuyun Yuningsih', 'pending'],
            ['Naila Syakira Hasna', 'Naila', 'P', 'Gowa', 'SD Islam Assalaam', 'Ridwan Saputra', 'Lestari Handayani', 'accepted'],
            ['Fauzan Abdurrahman', 'Fauzan', 'L', 'Maros', 'SDN Maros Baru 1', 'Dedi Mulyana', 'Iis Rohaeni', 'rejected'],
        ];

        foreach ($rows as $index => [$name, $nickname, $gender, $city, $school, $father, $mother, $status]) {
            PpdbRegistration::createWithNumber([
                'full_name' => $name,
                'nickname' => $nickname,
                'gender' => $gender,
                'birthplace' => $city,
                'birthdate' => now()->subYears(12)->subDays($index * 37)->toDateString(),
                'previous_school' => $school,
                'address' => 'Jl. Contoh No. '.($index + 10).', '.$city,
                'father_name' => $father,
                'mother_name' => $mother,
                'parent_phone' => '08'.random_int(1000000000, 9999999999),
                'parent_email' => Str::slug($father, '.').'@email.com',
                'grade_target' => 'Kelas VII',
                'status' => $status,
                'notes' => $status === 'rejected' ? 'Berkas tidak dilengkapi sampai batas waktu.' : '',
            ]);
        }
    }

    private function seedVisitorLogs(): void
    {
        if (VisitorLog::query()->exists()) {
            return;
        }

        $countries = [['Indonesia', 'ID', 40], ['Malaysia', 'MY', 4], ['Singapura', 'SG', 2]];

        for ($day = 13; $day >= 0; $day--) {
            foreach ($countries as [$country, $code, $weight]) {
                $visits = random_int((int) ($weight * 0.6), $weight);

                for ($i = 0; $i < $visits; $i++) {
                    VisitorLog::create([
                        'session_id' => Str::random(32),
                        'ip' => '192.0.2.'.random_int(1, 254),
                        'country' => $country,
                        'country_code' => $code,
                        'path' => collect(['/', 'berita', 'program', 'ppdb', 'kontak'])->random(),
                        'user_agent' => 'Mozilla/5.0 (seed)',
                        'created_at' => now()->subDays($day)->setTime(random_int(6, 21), random_int(0, 59)),
                    ]);
                }
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function json(array $rows): string
    {
        return (string) json_encode($rows, JSON_UNESCAPED_UNICODE);
    }
}
