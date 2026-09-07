# PRD — Website Sekolah (web-sekolahan)

> Product Requirements Document. Hasil bedah kode sumber per 2026-09-05.
> Catatan: dokumen ini TIDAK membahas styling/tema visual — style akan diganti baru. Yang didokumentasikan: arsitektur, fitur, data model, alur, dan perilaku sistem.

---

## 1. Ringkasan Produk

Website profil sekolah (SMP) multi-bahasa (id/en) dengan CMS internal, pendaftaran siswa baru online (PPDB), galeri, brosur, program unggulan, berita, monitoring dokumen Adiwiyata, asisten AI untuk guru (YPDH AI), dan AI writer untuk generate artikel berita.

Tiga area utama:

1. **Publik** — halaman terbuka tanpa login (home, tentang, program, berita, guru, galeri, kontak, PPDB).
2. **Panel Admin** — CMS penuh, kelola semua konten + pendaftar + pengguna + pengaturan.
3. **Panel Guru** — area terbatas guru: dashboard, berita milik sendiri, profil.

Dua "alat khusus" publik-tapi-terkunci-PIN:

- **Adiwiyata Monitor** — tracking kelengkapan dokumen Adiwiyata (snapshot struktur folder Google Drive), progres bisa dilihat publik, penyimpanan penilaian butuh PIN.
- **YPDH AI** — asisten AI untuk guru (chat + generate gambar), terkunci PIN per unit, key AI disimpan di server (tidak pernah bocor ke browser).

---

## 2. Stack Teknologi

### Backend
- **PHP 8.3+ / Laravel 13** (dev via Laravel Herd, sqlite lokal — `database/database.sqlite`)
- **Livewire 4** — semua halaman adalah full-page Livewire component (bukan API + SPA terpisah)
- **Database**: SQLite (dev), kompatibel MySQL/Postgres (prod). 14 migrasi.
- **Queue**: Laravel queue (`queue:listen`) untuk job async (mis. `GenerateNewsArticle`)
- **Auth**: session-based, role middleware custom (`EnsureRole`), 2FA TOTP via `pragmarx/google2fa-qrcode` + `bacon/bacon-qr-code`
- **File storage**: disk `public` untuk konten terbuka (gambar WebP), disk `local` privat untuk dokumen PPDB
- **Image pipeline**: `intervention/image` + service `App\Services\ImageProcessor` (resize, konversi WebP, thumbnail). Command `images:to-webp` untuk konversi massal.
- **Keamanan**: `mews/purifier` (sanitize HTML), `spatie/laravel-honeypot` (anti-spam form), rate limiter (PIN unlock), audit log, soft deletes
- **Mail**: `PpdbReceivedMail`, `PpdbAdminAlertMail`, `ContactAdminAlertMail`
- **i18n**: middleware `SetLocale`, whitelist locale `id|en`, route `/lang/{locale}`
- **SEO**: `SitemapController` (sitemap.xml dinamis), robots.txt dinamis, helper `App\Support\Seo`
- **Testing**: Pest 4, sqlite `:memory:`, `Carbon::setTestNow`

### Frontend (struktural — style akan diganti)
- **Vite + Tailwind CSS 4** (`@tailwindcss/vite`, `@tailwindcss/typography`)
- **Tiptap 3** — rich text editor (`components/rich-editor.blade.php`)
- Livewire full-page components, layout utama:
  - `components/layouts/public.blade.php` — situs publik
  - `layouts/panel.blade.php` — panel admin & guru
  - `layouts/auth.blade.php` — halaman login/dsb
- Komponen bersama: `icon`, `logo`, `toaster`, `confirm-delete`, `language-switcher`, `dark-mode-toggle`, `brand-styles`

### Integrasi eksternal
- **AI Gateway OpenAI-compatible** (`TintaGateway`, default `https://api.kryptonlab.web.id/v1`) — chat & image generation untuk YPDH AI. Base URL, key, model, system prompt semuanya di-set dari admin (tabel `settings`).
- **AiWriter** (provider dari `config/ai.php`, default gemini) — generate artikel berita utuh (judul, kategori, excerpt, konten HTML) dengan konteks identitas sekolah yang dikunci dari settings.
- **GeoIp** service — lookup negara dari IP untuk statistik pengunjung.
- **Google Drive snapshot** — `resources/data/adiwiyata-tree.json` sebagai pohon folder untuk monitor Adiwiyata.

---

## 3. Peran & Otorisasi

| Peran | Akses |
|---|---|
| Publik (anonim) | Semua route publik, kirim form kontak/kunjungan/PPDB, lihat progres Adiwiyata |
| Pemegang PIN Adiwiyata | Simpan/reset penilaian dokumen Adiwiyata (session flag, throttle 5 percobaan/5 menit) |
| Pemegang PIN YPDH AI | Chat & generate gambar via gateway AI |
| `guru` (login) | Dashboard guru, CRUD berita milik sendiri, edit profil sendiri |
| `admin` (login) | Semua akses guru + seluruh panel admin + kelola user + settings |

- Middleware: `auth`, `role:admin`, `role:guru` (`App\Http\Middleware\EnsureRole`)
- 2FA opsional per-akun: challenge setelah login bila aktif, halaman `/two-factor` untuk semua staf
- `User::isAdmin()`, `User::isGuru()`, `photoUrl()` fallback ke ui-avatars

---

## 4. Peta Route (Backend Surface)

### Publik (Livewire full-page)
| Route | Component | Nama |
|---|---|---|
| GET `/` | Public\Home | home |
| GET `/tentang-kami` | Public\About | about |
| GET `/program` | Public\ProgramsIndex | programs.index |
| GET `/program/{slug}` | Public\ProgramShow | programs.show |
| GET `/berita` | Public\NewsIndex | news.index |
| GET `/berita/{slug}` | Public\NewsShow | news.show |
| GET `/tim-guru` | Public\Teachers | teachers.index |
| GET `/galeri/{slug}` | Public\AlbumShow | gallery.album |
| GET `/kontak` | Public\Contact | contact |
| GET `/ppdb` | Public\Ppdb | ppdb.create |
| GET `/lang/{locale}` | closure (whitelist id/en) | locale.switch |
| GET `/sitemap.xml` | SitemapController | sitemap |
| GET `/robots.txt` | closure | robots |

### Adiwiyata (flag `.env` `ADIWIYATA_ENABLED`, PIN `.env` `ADIWIYATA_PIN`)
| Method | Route | Fungsi |
|---|---|---|
| GET | `/adiwiyata` | halaman (lock screen bila belum unlock) |
| GET | `/adiwiyata/data` | unduh snapshot pohon folder (butuh unlock) |
| POST | `/adiwiyata/unlock` | tukar PIN → session flag (throttled, `hash_equals`, regenerate session) |
| POST | `/adiwiyata/lock` | cabut flag |
| POST | `/adiwiyata/save` | upsert penilaian folder (ok/partial/empty + catatan) |
| POST | `/adiwiyata/reset` | reset penilaian |

### YPDH AI (PIN per unit via settings)
| Method | Route | Fungsi |
|---|---|---|
| GET | `/ypdh-ai` | halaman (lock screen bila belum unlock) |
| POST | `/ypdh-ai/unlock` / `lock` | PIN flow sama seperti Adiwiyata |
| POST | `/ypdh-ai/chat` | kirim pesan → Laravel → gateway AI (key server-side) |
| POST | `/ypdh-ai/image` | generate gambar (hanya bila model gambar diisi) |

### Auth (guest)
`/login`, `/forgot-password`, `/reset-password/{token}`, `/two-factor-challenge`; POST `/logout` (auth); GET `/two-factor` (auth) untuk pengaturan 2FA.

### Admin (`/admin`, middleware `auth` + `role:admin`)
`/` dashboard · `/programs` · `/news` · `/teachers` · `/brochures` · `/gallery` · `/gallery/{album}/photos` · `/ppdb` · `/ppdb/{registration}/document/{type}` (unduh dokumen privat, controller khusus) · `/contacts` · `/visits` · `/users` · `/icons` · `/settings`

### Guru (`/guru`, middleware `auth` + `role:guru`)
`/` dashboard · `/news` (berita milik sendiri) · `/profile`

---

## 5. Data Model (Backend)

### users
`name, email, password(hashed), role(admin|guru), phone, position, bio, photo, instagram, facebook, is_active(bool), two_factor_secret, two_factor_recovery_codes, email_verified_at, remember_token`
- Guru ditampilkan publik di halaman Tim Guru (yang `is_active`).

### settings (key-value, cache-forever per key)
Kunci yang dipakai sistem antara lain: `school_name`, `headmaster_name`, branding (logo/dsb), `ypdh_ai_base_url`, `ypdh_ai_key`, `ypdh_ai_model`, `ypdh_ai_model_image`, `ypdh_ai_system`, PIN YPDH, dll.
- `Setting::get/set/all_keyed/imageUrl()` — set meng-invalidate cache.

### programs
`title, slug(unique), icon(default 'book'), short_description, description(longtext), image, order, is_active`

### news
`user_id(FK cascade), title, slug(unique), category(default ARTIKEL; enum: KEGIATAN, PRESTASI, ARTIKEL, PENGUMUMAN), excerpt, content(longtext HTML tersanitize), image, published_at(date), is_published(bool)` + `SoftDeletes` + `Auditable`
- Fallback gambar: picsum seeded.

### ppdb_registrations
`registration_number(unique, format PPDB-YYYY-####), full_name, nickname, gender, birthplace, birthdate, previous_school, address, father_name, mother_name, parent_phone, parent_email, grade_target, kk_file, birth_certificate_file, status(pending|accepted|rejected), notes` + `SoftDeletes` + `Auditable`
- **Nomor pendaftaran**: dibuat aman-konkuren di `PpdbRegistration::createWithNumber()` — transaksi + `lockForUpdate` + retry 5x bila tabrakan unique.
- Dokumen (KK, akta) disimpan di disk `local` PRIVAT; diakses hanya via `PpdbDocumentController` dari panel admin.

### contact_messages
`name, email, phone, subject, message, is_read(bool)`

### visit_schedules
`name, email, phone, visit_date, participants(int), purpose, status(pending|...)`

### brochures
`title, subtitle, preview_image, file(PDF opsional), order, is_active` + `brochure_images` (halaman/gambar brosur, migrasi terpisah)

### gallery_albums / gallery_photos
- albums: `title, slug(unique), description, cover_image, order, is_published`
- photos: `album_id(FK cascade), image(max 1600px), thumbnail(~480px), caption, order`

### event_themes
Tema event + `background_image` (mis. tema PPDB/hari besar).

### visitor_logs
`session_id(64, idx), ip(45, idx), country, country_code, path(500), user_agent(500), created_at(idx)` — diisi middleware `TrackVisitor`, agregasi via `VisitorStats` (dashboard admin).

### adiwiyata_assessments
`folder_key(unique, path folder Drive s/d 255 char), status(ok|partial|empty), note`

### audit_logs
Diisi trait `Auditable` (dipakai News, PpdbRegistration, dsb) — jejak create/update/delete oleh user.

---

## 6. Spesifikasi Fitur

### 6.1 Publik — Home
Komponen dinamis dari settings/program/berita terbit/event theme aktif/galeri. Global search (component `GlobalSearch`) mencari lintas konten.

### 6.2 Publik — Program
Index (daftar program `is_active` urut `order`) + Show by slug (detail rich content).

### 6.3 Publik — Berita
Index berpaginasi + filter kategori; Show by slug; hanya `is_published` dan `published_at` lewat.

### 6.4 Publik — Tim Guru
Daftar user role guru yang aktif: foto (fallback avatar), posisi, bio, sosial media.

### 6.5 Publik — Galeri
Album terbit → daftar foto (thumbnail → full), caption, urutan.

### 6.6 Publik — Kontak
Form kontak (honeypot anti-spam via `WithSpamProtection`), simpan `contact_messages`, kirim email alert ke admin (`ContactAdminAlertMail`). Juga form jadwal kunjungan (`VisitForm`) → `visit_schedules`.

### 6.7 Publik — PPDB (formulir pendaftaran)
Component `PpdbForm`:
- Input data calon siswa + orang tua (field sesuai model).
- Upload **KK** (PDF) dan **Akta** (JPG/PNG) — disimpan privat (`ppdb/kk`, `ppdb/akte`).
- Submit → `createWithNumber()` (nomor aman konkuren) → email konfirmasi ke orang tua (`PpdbReceivedMail`) + alert ke admin (`PpdbAdminAlertMail`).
- Tampilkan nomor pendaftaran ke pendaftar.

### 6.8 Alat — Adiwiyata Monitor
- Halaman publik menampilkan pohon folder (dari `resources/data/adiwiyata-tree.json`) + status tiap folder.
- Semua data terkunci di balik PIN bersama (env). Unlock: throttle 5/300s, `hash_equals`, session regenerate (anti fixation), gagal-tertutup bila PIN kosong (404).
- Save: upsert `adiwiyata_assessments` (folder_key, status, note).
- Route selalu terdaftar (aman untuk `route:cache`); flag dicek di controller.

### 6.9 Alat — YPDH AI
- Pola PIN sama. Setelah unlock: UI chat + (opsional) tab gambar.
- `TintaGateway`: semua kredensial dari `settings` (bisa diubah admin); **key tidak pernah dikirim ke browser** — browser bicara ke Laravel saja.
- Endpoint otomatis: bila base URL sudah diakhiri `/chat/completions` atau `/images/generations` dipakai apa adanya.
- `models()` mengambil daftar model gateway (admin bisa coba sebelum simpan).
- `chat()` timeout 180s, system prompt default konteks Kurikulum Merdeka (bisa dioverride dari settings).

### 6.10 Panel Admin — Dashboard
Statistik pengunjung (`VisitorStats` + GeoIp: negara, tren), ringkasan konten, pendaftar PPDB terbaru, pesan masuk belum dibaca.

### 6.11 Panel Admin — CRUD konten
- **Programs, News, Teachers, Brochures, Gallery (+ AlbumPhotos)**: CRUD penuh via Livewire, upload gambar → `ImageProcessor` (resize + WebP), rich editor Tiptap (konten HTML disanitize `HtmlSanitizer`/`mews/purifier`), konfirmasi hapus (`WithDeleteConfirm`), notifikasi toast (`WithNotifications`).
- **Icon Library**: pustaka ikon untuk field `icon` (program, dsb).
- **News**: slug unik, kategori, jadwal terbit, soft delete, audit. Ada tombol **AI Generate** → job `GenerateNewsArticle` (queue) via `AiWriter` (konteks sekolah dikunci dari settings, output JSON ketat, konten disanitize).

### 6.12 Panel Admin — PPDB
Daftar pendaftar (filter status pending/accepted/rejected, pencarian), detail, ubah status + catatan, **unduh dokumen privat** via `PpdbDocumentController` (otorisasi admin), **ekspor CSV** (`App\Exports\PpdbCsv`).

### 6.13 Panel Admin — Contacts & Visits
Inbox pesan kontak (tandai dibaca), kelola jadwal kunjungan (ubah status).

### 6.14 Panel Admin — Users
Kelola akun admin/guru: CRUD, aktif/nonaktif, reset password, (2FA dikelola masing-masing akun di `/two-factor`).

### 6.15 Panel Admin — Settings
Semua kunci `settings`: identitas sekolah (nama, kepala sekolah), branding (logo dsb, upload → WebP), kredensial YPDH AI (base URL, key, model chat, model gambar, system prompt, PIN), PIN Adiwiyata (env), dsb. Tombol "ambil daftar model" untuk menguji kredensial gateway sebelum menyimpan.

### 6.16 Panel Guru
- Dashboard ringkas.
- **My News**: CRUD berita hanya milik sendiri (`user_id` = diri).
- **Profile**: edit data diri, foto, sosial media (tampil di halaman Tim Guru).

### 6.17 Command & Job
- `php artisan images:to-webp` — konversi massal gambar ke WebP.
- `php artisan make:admin` (`CreateAdminUser`) — bootstrap akun admin.
- Job `GenerateNewsArticle` — generate artikel AI di background.

---

## 7. Frontend — Struktur Halaman (tanpa style)

Layout publik: header (logo, nav: Beranda, Tentang, Program, Berita, Tim Guru, Galeri, Kontak, PPDB), language switcher, global search, footer (kontak, sosial, sitemap).

Halaman yang harus ada saat restyle:
1. Home — hero, program unggulan, berita terbaru, galeri cuplikan, CTA PPDB, banner event theme.
2. Tentang Kami — profil, visi-misi (dari settings/konten statis).
3. Program — grid daftar + halaman detail.
4. Berita — list + filter kategori + detail artikel (typography).
5. Tim Guru — grid kartu guru.
6. Galeri album — grid foto + lightbox.
7. Kontak — info + form kontak + form kunjungan + peta.
8. PPDB — info alur + formulir multi-bagian + upload dokumen + tampilan nomor pendaftaran sukses.
9. Adiwiyata — lock screen (PIN) + pohon folder dengan status badge + editor penilaian.
10. YPDH AI — lock screen + chat interface + tab gambar.
11. Auth — login, lupa/reset password, challenge 2FA, pengaturan 2FA.
12. Panel (admin & guru) — sidebar sesuai role, tabel data + form modal/inline untuk tiap modul di §6.10–6.16, toaster global, confirm-delete global.

Komponen reusable yang sudah ada dan harus dipertahankan fungsinya: `icon`, `logo`, `toaster`, `confirm-delete`, `rich-editor` (Tiptap), `language-switcher`, `dark-mode-toggle`, `brand-styles`.

---

## 8. Perilaku Lintas-Bidang (Non-fungsional)

- **Keamanan**: CSRF (bawaan), honeypot pada form publik, sanitasi HTML pada semua konten rich text & output AI, rate limit PIN, dokumen PPDB tidak pernah publik, key AI server-side only, `hash_equals` untuk PIN, session regenerate saat eskalasi hak.
- **Performa**: cache-forever untuk settings (invalidate on write), gambar WebP + thumbnail, queue untuk AI & email.
- **SEO**: sitemap dinamis, robots dinamis, slug unik, helper Seo untuk meta.
- **i18n**: id/en via session, whitelist locale.
- **Audit & retensi**: soft deletes + audit_logs pada entitas penting (news, PPDB).
- **Reliabilitas**: nomor PPDB aman konkuren (lock + retry); AI writer melempar error jelas bila provider/key belum diset.

## 9. Batasan & Asumsi
- Multi-unit: beberapa tool (Adiwiyata, YPDH AI) diaktifkan per unit lewat `.env`.
- Style/tema visual sengaja tidak didokumentasikan — akan diganti menyeluruh; seluruh struktur route, komponen, dan data tetap menjadi kontrak.
- Bahasa konten utama: Indonesia; UI dwibahasa id/en.

---
*Dokumen ini diturunkan langsung dari kode: routes/web.php, seluruh app/Livewire, app/Models, database/migrations, app/Services.*
