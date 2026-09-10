<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import PhoneCall from '@lucide/svelte/icons/phone-call';
    import Quote from '@lucide/svelte/icons/quote';
    import AppHead from '@/components/AppHead.svelte';
    import Icon from '@/components/public/Icon.svelte';
    import NewsCard from '@/components/public/NewsCard.svelte';
    import ProgramCard from '@/components/public/ProgramCard.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';
    import VideoCarousel, {
        type Video,
    } from '@/components/public/VideoCarousel.svelte';
    import { reveal } from '@/lib/reveal';
    import { schoolState } from '@/lib/school.svelte';
    import { facilities, slides } from '@/lib/site';
    import type {
        GalleryPhoto,
        NewsItem,
        ProgramCard as ProgramCardData,
        Stat,
    } from '@/types/content';

    let {
        programs = [],
        news = [],
        photos = [],
        stats = [],
        albumSlug = '',
        about = '',
        highlights = [],
        slides: slideProps = [],
        aboutPoints = [],
        quote = {},
        cta = {},
        videos = [],
    }: {
        programs?: ProgramCardData[];
        news?: NewsItem[];
        photos?: GalleryPhoto[];
        stats?: Stat[];
        albumSlug?: string;
        about?: string | null;
        highlights?: Array<{ icon: string; title: string; text: string }>;
        slides?: Array<{
            eyebrow: string;
            title: string;
            accent: string | null;
            title_end: string | null;
            text: string;
            image: string | null;
        }>;
        aboutPoints?: Array<{ icon: string; title: string; text: string }>;
        quote?: Record<string, string | null>;
        cta?: Record<string, string | null>;
        videos?: Video[];
    } = $props();

    const school = $derived(page.props.school ?? {});
    const heroFallback = $derived(page.props.branding?.hero_image ?? '');
    const identity = schoolState();

    // Every list falls back to the built-in copy while Pengaturan is empty.
    const cards = $derived(highlights.length > 0 ? highlights : facilities);
    const deck = $derived(
        slideProps.length > 0
            ? slideProps
            : slides.map((item) => ({
                  eyebrow: item.eyebrow,
                  title: item.title,
                  accent: item.accent,
                  title_end: item.titleEnd,
                  text: item.text,
                  image: item.image,
              })),
    );
    const defaultPoints = [
        {
            icon: 'book-marked',
            title: 'Kelas kecil, pendampingan personal',
            text: 'Maksimal 24 siswa per kelas dengan laporan perkembangan bulanan ke orang tua.',
        },
        {
            icon: 'sprout',
            title: 'Projek nyata, bukan hafalan saja',
            text: 'Setiap semester siswa menuntaskan satu projek P5 yang dipakai lingkungan sekolah.',
        },
    ];

    const points = $derived(aboutPoints.length > 0 ? aboutPoints : defaultPoints);

    let index = $state(0);
    const slide = $derived(deck[Math.min(index, deck.length - 1)]);

    function move(step: number): void {
        index = (index + step + deck.length) % deck.length;
    }
</script>

<AppHead title="Beranda" />

<!-- Hero -->
<section class="relative isolate overflow-hidden bg-brand-deep">
    {#key index}
        <img
            src={slide.image ?? heroFallback}
            alt=""
            class="absolute inset-0 size-full object-cover opacity-40 motion-safe:animate-in motion-safe:fade-in motion-safe:duration-700"
        />
    {/key}
    <div class="absolute inset-0 bg-gradient-to-r from-brand-deep via-brand-deep/90 to-brand/40"></div>

    <div class="shell relative flex min-h-[560px] items-center py-20 lg:min-h-[620px] lg:pb-44">
        <div class="max-w-2xl">
            {#key index}
                <p use:reveal={{ y: 12 }} class="eyebrow">
                    <span class="h-px w-6 bg-sun"></span>{identity.fill(slide.eyebrow)}
                </p>
                <h1
                    use:reveal={{ delay: 90, y: 24 }}
                    class="mt-4 font-display text-4xl leading-[1.08] font-extrabold text-white sm:text-5xl lg:text-6xl"
                >
                    {slide.title}
                    {#if slide.accent}<span class="text-sun">{slide.accent}</span>{/if}
                    {slide.title_end ?? ''}
                </h1>
                <p
                    use:reveal={{ delay: 180 }}
                    class="mt-5 max-w-xl text-sm leading-relaxed text-white/80 sm:text-base"
                >
                    {slide.text}
                </p>
            {/key}
            <div use:reveal={{ delay: 260 }} class="mt-8 flex flex-wrap gap-3">
                <Link href="/ppdb" class="btn-sun">
                    Daftar PPDB
                    <ArrowRight class="size-4" />
                </Link>
                <Link href="/tentang-kami" class="btn-outline-light">
                    Profil sekolah
                </Link>
            </div>
        </div>
    </div>

    {#if deck.length > 1}
        <div class="absolute inset-x-0 top-1/2 hidden -translate-y-1/2 lg:block">
        <div class="shell flex justify-end gap-3">
            <button
                type="button"
                onclick={() => move(-1)}
                class="grid size-11 place-items-center rounded-full bg-white/15 text-white transition hover:bg-sun focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
            >
                <span class="sr-only">Slide sebelumnya</span>
                <ChevronLeft class="size-5" />
            </button>
            <button
                    type="button"
                    onclick={() => move(1)}
                    class="grid size-11 place-items-center rounded-full bg-white/15 text-white transition hover:bg-sun focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                >
                    <span class="sr-only">Slide berikutnya</span>
                    <ChevronRight class="size-5" />
                </button>
            </div>
        </div>
    {/if}
</section>

<!-- Facility cards, overlapping the hero -->
<section class="bg-white pb-16">
    <div class="shell relative z-10 -mt-12 grid gap-5 sm:grid-cols-2 lg:-mt-28 lg:grid-cols-4">
        {#each cards as facility, cardIndex (facility.title)}
            <article
                use:reveal={{ delay: cardIndex * 90 }}
                class="rounded-2xl bg-white p-6 shadow-xl shadow-ink/10"
            >
                <span class="grid size-12 place-items-center rounded-xl bg-brand text-white">
                    <Icon name={facility.icon} class="size-6" />
                </span>
                <h3 class="mt-4 font-display text-base font-bold text-ink">
                    {facility.title}
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                    {facility.text}
                </p>
            </article>
        {/each}
    </div>
</section>

<!-- About -->
<section class="bg-white pb-20">
    <div class="shell grid items-center gap-12 lg:grid-cols-2">
        <div use:reveal={{ y: 24 }} class="relative pb-10 sm:pb-0">
            <div class="grid gap-4 sm:grid-cols-2">
                <img
                    src="https://picsum.photos/seed/dh-about-1/700/900"
                    alt="Guru mendampingi siswa di kelas"
                    loading="lazy"
                    class="h-72 w-full rounded-2xl object-cover sm:h-[27rem]"
                />
                <div class="grid gap-4">
                    <img
                        src="https://picsum.photos/seed/dh-about-2/600/600"
                        alt="Siswa berdiskusi kelompok"
                        loading="lazy"
                        class="h-40 w-full rounded-2xl object-cover sm:h-[13rem]"
                    />
                    <img
                        src="https://picsum.photos/seed/dh-about-3/600/600"
                        alt="Praktikum di laboratorium"
                        loading="lazy"
                        class="h-40 w-full rounded-2xl object-cover sm:h-[13rem]"
                    />
                </div>
            </div>
            <div class="absolute bottom-0 left-0 flex items-center gap-3 rounded-2xl bg-sun px-5 py-4 text-white shadow-xl sm:-bottom-6 sm:left-6">
                <Icon name="trophy" class="size-8" />
                <p class="font-display text-sm leading-tight font-bold">
                    21 tahun<br />melayani pendidikan
                </p>
            </div>
        </div>

        <div>
            <SectionHead
                align="left"
                eyebrow="Tentang kami"
                title="Sekolah yang menumbuhkan"
                accent="adab lebih dulu"
                text={about ||
                    `${identity.name} berada di bawah ${identity.foundation}. Kurikulum Merdeka dengan penguatan tahfizh, riset sederhana, dan kepedulian lingkungan.`}
            />

            <div use:reveal={{ delay: 120 }} class="mt-8 space-y-5">
                {#each points as point (point.title)}
                    <div class="flex gap-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-mist text-brand">
                            <Icon name={point.icon} class="size-5" />
                        </span>
                        <div>
                            <h3 class="font-display text-base font-bold text-ink">
                                {point.title}
                            </h3>
                            <p class="mt-1 text-sm leading-relaxed text-ink-soft">
                                {point.text}
                            </p>
                        </div>
                    </div>
                {/each}
            </div>

            <figure use:reveal={{ delay: 200 }} class="mt-8 rounded-2xl bg-mist p-6">
                <Quote class="size-6 text-sun" />
                <blockquote class="mt-3 text-sm leading-relaxed text-ink">
                    {quote.home_quote ??
                        'Anak-anak kami pulang membawa cerita tentang apa yang mereka kerjakan, bukan hanya nilai yang mereka dapat.'}
                </blockquote>
                <figcaption class="mt-3 font-display text-xs font-bold text-ink-soft uppercase">
                    {quote.home_quote_by ?? 'Wali murid'}
                </figcaption>
            </figure>

            <div use:reveal={{ delay: 260 }} class="mt-8 flex flex-wrap items-center gap-6">
                <Link href="/tentang-kami" class="btn-sun">
                    Selengkapnya
                    <ArrowRight class="size-4" />
                </Link>
                <a href="tel:{school.phone ?? ''}" class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-full bg-brand text-white">
                        <PhoneCall class="size-5" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-xs text-ink-soft">Tanya panitia</span>
                        <span class="block font-display text-sm font-bold text-ink">
                            {school.phone}
                        </span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="relative isolate overflow-hidden bg-brand">
    <img
        src="https://picsum.photos/seed/dh-stats/1600/600"
        alt=""
        class="absolute inset-0 size-full object-cover opacity-20"
    />
    <div class="absolute inset-0 bg-brand/85"></div>
    <div class="shell relative grid gap-8 py-16 sm:grid-cols-2 lg:grid-cols-4">
        {#each stats as stat, statIndex (stat.label)}
            <div use:reveal={{ delay: statIndex * 90, y: 20 }} class="text-center">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-sun text-white shadow-lg shadow-brand-deep/30">
                    <Icon name={stat.icon} class="size-7" />
                </span>
                <p class="mt-4 font-display text-4xl font-extrabold text-white">
                    {stat.value}
                </p>
                <p class="mt-1 text-sm text-white/80">{stat.label}</p>
            </div>
        {/each}
    </div>
</section>

<!-- Programs -->
<section class="bg-mist/60 py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Program kami"
            title="Yang siswa kerjakan"
            accent="setiap pekan"
            text="Enam program inti yang berjalan sepanjang tahun ajaran, masing-masing dengan pembina dan target yang terukur."
        />
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {#each programs as program, programIndex (program.slug)}
                <ProgramCard {program} delay={programIndex * 90} />
            {/each}
        </div>
        <div use:reveal class="mt-10 text-center">
            <Link href="/program" class="btn-brand">
                Semua program
                <ArrowRight class="size-4" />
            </Link>
        </div>
    </div>
</section>

<!-- Video gallery, above the news section -->
<VideoCarousel {videos} />

<!-- News -->
<section class="bg-white py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Kabar sekolah"
            title="Berita dan"
            accent="pengumuman"
            text="Kegiatan, prestasi, dan informasi resmi dari panitia serta humas sekolah."
        />
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {#each news as item, newsIndex (item.slug)}
                <NewsCard {item} delay={newsIndex * 90} />
            {/each}
        </div>
        <div use:reveal class="mt-10 text-center">
            <Link href="/berita" class="btn-brand">
                Semua berita
                <ArrowRight class="size-4" />
            </Link>
        </div>
    </div>
</section>

<!-- Gallery strip -->
<section class="bg-mist/60 py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Galeri"
            title="Sehari-hari di"
            accent={identity.shortName}
        />
        <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            {#each photos as photo, photoIndex (photo.id)}
                <div use:reveal={{ delay: photoIndex * 70, y: 12 }}>
                    <Link
                        href={`/galeri/${albumSlug}`}
                        class="group relative block aspect-square overflow-hidden rounded-xl"
                    >
                        <img
                            src={photo.thumbnail}
                            alt={photo.caption ?? ''}
                            loading="lazy"
                            class="size-full object-cover transition duration-500 group-hover:scale-110"
                        />
                        <span
                            class="absolute inset-0 grid place-items-center bg-brand-deep/70 p-2 text-center font-display text-xs font-bold text-white opacity-0 transition group-hover:opacity-100"
                        >
                            {photo.caption}
                        </span>
                    </Link>
                </div>
            {/each}
        </div>
    </div>
</section>

<!-- PPDB CTA -->
<section class="bg-white pb-20">
    <div class="shell">
        <div class="relative isolate overflow-hidden rounded-3xl bg-brand-deep px-8 py-14 text-center sm:px-14">
            <img
                src="https://picsum.photos/seed/dh-cta/1400/500"
                alt=""
                class="absolute inset-0 size-full object-cover opacity-25"
            />
            <div class="absolute inset-0 bg-brand-deep/80"></div>
            <div use:reveal={{ y: 20 }} class="relative mx-auto max-w-2xl">
                <p class="eyebrow justify-center">
                    <span class="h-px w-6 bg-sun"></span>{cta.ppdb_cta_eyebrow ??
                        'Pendaftaran siswa baru'}
                </p>
                <h2 class="mt-3 font-display text-3xl leading-tight font-extrabold text-white sm:text-4xl">
                    {cta.ppdb_cta_title ?? 'Formulir PPDB sudah dibuka.'}
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-white/80">
                    {cta.ppdb_cta_text ??
                        'Isi formulir online, unggah kartu keluarga dan akta, lalu tunggu kabar verifikasi dalam dua hari kerja.'}
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <Link href="/ppdb" class="btn-sun">
                        Isi formulir
                        <ArrowRight class="size-4" />
                    </Link>
                    <a
                        href={`https://wa.me/${(school.whatsapp ?? '').replace(/[^0-9]/g, '')}`}
                        class="btn-outline-light"
                    >
                        Tanya via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
