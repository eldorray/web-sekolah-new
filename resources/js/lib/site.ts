/**
 * Static chrome for the public site: navigation, hero copy, and the two
 * PIN-locked tools. Everything data-driven now comes from Inertia props.
 */

export type NavItem = { label: string; href: string };

export const nav: NavItem[] = [
    { label: 'Beranda', href: '/' },
    { label: 'Tentang Kami', href: '/tentang-kami' },
    { label: 'Program', href: '/program' },
    { label: 'Berita', href: '/berita' },
    { label: 'Tim Guru', href: '/tim-guru' },
    { label: 'Galeri', href: '/galeri/kegiatan-sekolah' },
    { label: 'Kontak', href: '/kontak' },
];

/** `key` points at the settings entry that overrides `href` when filled. */
export const socials = [
    {
        label: 'Instagram',
        href: 'https://instagram.com',
        icon: 'instagram',
        key: 'instagram',
    },
    {
        label: 'Facebook',
        href: 'https://facebook.com',
        icon: 'facebook',
        key: 'facebook',
    },
    {
        label: 'YouTube',
        href: 'https://youtube.com',
        icon: 'youtube',
        key: 'youtube',
    },
    {
        label: 'WhatsApp',
        href: 'https://wa.me/',
        icon: 'phone',
        key: 'whatsapp_url',
    },
];

/** `{school}`, `{foundation}` and `{short}` are filled from Pengaturan. */
export const slides = [
    {
        eyebrow: 'Selamat datang di {school}',
        title: 'Menyiapkan generasi',
        accent: 'berakhlak',
        titleEnd: 'dan berprestasi',
        text: 'Kurikulum Merdeka berpadu pembinaan tahfizh dan karakter. Kelas kecil, satu guru mendampingi 20 siswa, sehingga tiap anak terpantau.',
        image: 'https://picsum.photos/seed/dh-hero-1/1600/900',
    },
    {
        eyebrow: 'Pendaftaran siswa baru 2027/2028',
        title: 'Gelombang I dibuka',
        accent: 'Januari',
        titleEnd: 'setiap tahun',
        text: 'Kuota 96 siswa untuk empat kelas. Seleksi berkas, tes baca Al-Qur’an, dan wawancara orang tua. Formulir bisa diisi online.',
        image: 'https://picsum.photos/seed/dh-hero-2/1600/900',
    },
    {
        eyebrow: 'Sekolah Adiwiyata',
        title: 'Belajar merawat',
        accent: 'lingkungan',
        titleEnd: 'sejak kelas tujuh',
        text: 'Bank sampah, kebun sekolah, dan bengkel kompos dikelola siswa. Progres dokumen Adiwiyata terbuka untuk publik.',
        image: 'https://picsum.photos/seed/dh-hero-3/1600/900',
    },
];

export const facilities = [
    {
        icon: 'graduation-cap',
        title: 'Beasiswa prestasi',
        text: 'Potongan SPP hingga 100% untuk juara olimpiade dan hafizh 10 juz.',
    },
    {
        icon: 'users',
        title: 'Guru pendamping',
        text: 'Rasio 1 : 20. Setiap wali kelas melapor ke orang tua tiap bulan.',
    },
    {
        icon: 'library-big',
        title: 'Perpustakaan',
        text: '6.400 judul buku, ruang baca ber-AC, dan katalog digital.',
    },
    {
        icon: 'flask-conical',
        title: 'Laboratorium',
        text: 'Lab IPA, komputer, dan studio multimedia untuk projek P5.',
    },
];

export const stats = [
    { icon: 'book-open', value: '38', label: 'Guru & tenaga pendidik' },
    { icon: 'users', value: '512', label: 'Siswa aktif' },
    { icon: 'trophy', value: '146', label: 'Piala tingkat kota & provinsi' },
    { icon: 'calendar-days', value: '21', label: 'Tahun melayani' },
];

export const ppdbSteps = [
    {
        title: 'Isi formulir online',
        text: 'Lengkapi data calon siswa dan orang tua, lalu unggah KK serta akta kelahiran.',
    },
    {
        title: 'Verifikasi berkas',
        text: 'Panitia memeriksa berkas dalam 2 hari kerja dan mengabari lewat WhatsApp.',
    },
    {
        title: 'Tes & wawancara',
        text: 'Tes baca Al-Qur’an, tes akademik dasar, dan wawancara bersama orang tua.',
    },
    {
        title: 'Pengumuman',
        text: 'Hasil dikirim ke email pendaftar dan diumumkan di halaman pengumuman.',
    },
];

/** Snapshot pohon folder Adiwiyata (contoh, nanti dari Google Drive). */
export const adiwiyataTree = [
    {
        key: 'IKPA',
        title: 'IKPA — Perencanaan & Kebijakan',
        children: [
            { key: 'IKPA/1', title: 'SK Tim Adiwiyata', status: 'ok' },
            {
                key: 'IKPA/2',
                title: 'Dokumen KTSP bermuatan PRLH',
                status: 'ok',
            },
            { key: 'IKPA/3', title: 'RKAS bermuatan PRLH', status: 'partial' },
        ],
    },
    {
        key: 'IKPB',
        title: 'IKPB — Pelaksanaan Gerakan PBLHS',
        children: [
            { key: 'IKPB/1', title: 'Kebersihan & sanitasi', status: 'ok' },
            { key: 'IKPB/2', title: 'Pengelolaan sampah', status: 'ok' },
            {
                key: 'IKPB/3',
                title: 'Penanaman & pemeliharaan pohon',
                status: 'partial',
            },
            { key: 'IKPB/4', title: 'Konservasi air', status: 'empty' },
            { key: 'IKPB/5', title: 'Konservasi energi', status: 'empty' },
        ],
    },
    {
        key: 'IKPC',
        title: 'IKPC — Pemantauan & Evaluasi',
        children: [
            { key: 'IKPC/1', title: 'Laporan berkala tim', status: 'partial' },
            {
                key: 'IKPC/2',
                title: 'Umpan balik warga sekolah',
                status: 'empty',
            },
        ],
    },
];

export const adiwiyataStatusLabels = {
    ok: 'Lengkap',
    partial: 'Sebagian',
    empty: 'Kosong',
} as const;
