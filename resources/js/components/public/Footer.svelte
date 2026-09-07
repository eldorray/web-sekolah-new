<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Clock from '@lucide/svelte/icons/clock';
    import Mail from '@lucide/svelte/icons/mail';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import PhoneCall from '@lucide/svelte/icons/phone-call';
    import SocialIcon from '@/components/public/SocialIcon.svelte';
    import { nav, socials } from '@/lib/site';

    const school = $derived(page.props.school ?? {});
    const programs = $derived(page.props.navPrograms ?? []);
    const logo = $derived(page.props.branding?.logo ?? null);
</script>

<footer class="bg-brand-deep text-white/80">
    <div class="shell grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
        <div>
            {#if logo}
                <img
                    src={logo}
                    alt="Logo {school.school_name ?? 'sekolah'}"
                    class="mb-3 h-12 w-auto object-contain"
                />
            {/if}
            <p class="font-display text-xl font-extrabold text-white">
                {school.school_name ?? 'Sekolah'}
            </p>
            <p class="mt-3 text-sm leading-relaxed">
                {school.foundation_name ?? ''}. Membina siswa lewat akademik,
                tahfizh, dan projek lingkungan.
            </p>
            <div class="mt-5 flex gap-2">
                {#each socials as social (social.label)}
                    <a
                        href={(school[social.key] as string) || social.href}
                        target="_blank"
                        rel="noreferrer"
                        aria-label={social.label}
                        class="grid size-9 place-items-center rounded-full bg-white/10 transition hover:bg-sun hover:text-white focus-visible:ring-2 focus-visible:ring-sun focus-visible:outline-none"
                    >
                        <SocialIcon name={social.icon} />
                    </a>
                {/each}
            </div>
        </div>

        <nav aria-labelledby="footer-nav">
            <p id="footer-nav" class="font-display text-sm font-bold tracking-wide text-white uppercase">
                Halaman
            </p>
            <ul class="mt-4 space-y-2.5 text-sm">
                {#each nav as item (item.href)}
                    <li>
                        <Link href={item.href} class="transition hover:text-sun">
                            {item.label}
                        </Link>
                    </li>
                {/each}
            </ul>
        </nav>

        <nav aria-labelledby="footer-programs">
            <p id="footer-programs" class="font-display text-sm font-bold tracking-wide text-white uppercase">
                Program
            </p>
            <ul class="mt-4 space-y-2.5 text-sm">
                {#each programs as program (program.slug)}
                    <li>
                        <Link
                            href={`/program/${program.slug}`}
                            class="transition hover:text-sun"
                        >
                            {program.title}
                        </Link>
                    </li>
                {/each}
            </ul>
        </nav>

        <div>
            <p class="font-display text-sm font-bold tracking-wide text-white uppercase">
                Hubungi kami
            </p>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex gap-3">
                    <MapPin class="mt-0.5 size-4 shrink-0 text-sun" />
                    <span>{school.address ?? '—'}</span>
                </li>
                <li class="flex gap-3">
                    <PhoneCall class="mt-0.5 size-4 shrink-0 text-sun" />
                    <span>{school.phone ?? '—'} · WA {school.whatsapp ?? '—'}</span>
                </li>
                <li class="flex gap-3">
                    <Mail class="mt-0.5 size-4 shrink-0 text-sun" />
                    <a href="mailto:{school.email ?? ''}" class="hover:text-sun">
                        {school.email ?? '—'}
                    </a>
                </li>
                <li class="flex gap-3">
                    <Clock class="mt-0.5 size-4 shrink-0 text-sun" />
                    <span>{school.hours ?? '—'}</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="shell flex flex-col items-center justify-between gap-2 py-5 text-xs sm:flex-row">
            <p>
                © {new Date().getFullYear()}
                {school.school_name ?? 'Sekolah'}. Seluruh hak dilindungi.
            </p>
            <p class="flex gap-4">
                <a href="/sitemap.xml" class="hover:text-sun">Sitemap</a>
                <Link href="/adiwiyata" class="hover:text-sun">Adiwiyata</Link>
                <Link href="/login" class="hover:text-sun">Login staf</Link>
            </p>
        </div>
    </div>
</footer>
