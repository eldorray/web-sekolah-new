<script lang="ts">
    import Check from '@lucide/svelte/icons/check';
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import { schoolState } from '@/lib/school.svelte';
    import Icon from '@/components/public/Icon.svelte';
    import PageHero from '@/components/public/PageHero.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';
    import type { Stat } from '@/types/content';

    let {
        profile = {},
        stats = [],
        images = {},
    }: {
        profile?: Record<string, string | null>;
        stats?: Stat[];
        images?: Record<string, string | null>;
    } = $props();

    const school = $derived(page.props.school ?? {});
    const identity = schoolState();

    const misi = $derived(
        (profile.mission ?? '')
            .split('\n')
            .map((line) => line.trim())
            .filter((line) => line !== ''),
    );

    const defaultMisi = [
        'Menegakkan pembiasaan ibadah dan adab dalam kegiatan harian sekolah.',
        'Menyelenggarakan pembelajaran aktif berbasis projek dan riset sederhana.',
        'Membina hafalan Al-Qur’an minimal tiga juz secara mutqin.',
        'Menumbuhkan kepedulian lingkungan lewat program Adiwiyata.',
        'Membangun kemitraan terbuka dengan orang tua dan masyarakat.',
    ];

    const missionList = $derived(misi.length > 0 ? misi : defaultMisi);
</script>

<AppHead title="Tentang Kami" />
<PageHero
    title="Tentang Kami"
    crumb="Tentang Kami"
    image={images.hero ?? undefined}
/>

<section class="bg-white py-20">
    <div class="shell grid items-center gap-12 lg:grid-cols-2">
        {#if images.photo}
            <img
                use:reveal={{ y: 24 }}
                src={images.photo}
                alt="Gedung {identity.name}"
                class="w-full rounded-2xl object-cover"
            />
        {:else}
            <div
                use:reveal={{ y: 24 }}
                class="grid aspect-4/3 w-full place-items-center rounded-2xl bg-mist px-8 text-center text-sm text-ink-soft"
            >
                Unggah foto sekolah lewat Pengaturan → Branding.
            </div>
        {/if}
        <div>
            <SectionHead
                align="left"
                eyebrow="Profil singkat"
                title={profile.about_heading || 'Profil'}
                accent={profile.about_heading_accent || identity.shortName}
                text={profile.about_body ||
                    `${identity.name} dikelola ${identity.foundation}. Isi profil sekolah lewat Pengaturan agar bagian ini terisi.`}
            />
            <dl use:reveal={{ delay: 120 }} class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-mist p-4">
                    <dt class="text-xs font-semibold text-ink-soft uppercase">NPSN</dt>
                    <dd class="mt-1 font-display text-lg font-bold text-ink">
                        {profile.npsn ?? '—'}
                    </dd>
                </div>
                <div class="rounded-xl bg-mist p-4">
                    <dt class="text-xs font-semibold text-ink-soft uppercase">Akreditasi</dt>
                    <dd class="mt-1 font-display text-lg font-bold text-ink">
                        {profile.accreditation ?? '—'}
                    </dd>
                </div>
                <div class="rounded-xl bg-mist p-4">
                    <dt class="text-xs font-semibold text-ink-soft uppercase">Kurikulum</dt>
                    <dd class="mt-1 font-display text-lg font-bold text-ink">
                        {profile.curriculum ?? '—'}
                    </dd>
                </div>
                <div class="rounded-xl bg-mist p-4">
                    <dt class="text-xs font-semibold text-ink-soft uppercase">Jam belajar</dt>
                    <dd class="mt-1 font-display text-sm font-bold text-ink">
                        {profile.hours ?? school.hours ?? '—'}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<section class="bg-mist/60 py-20">
    <div class="shell grid gap-8 lg:grid-cols-5">
        <div use:reveal={{ y: 20 }} class="rounded-2xl bg-brand p-8 text-white lg:col-span-2">
            <p class="eyebrow text-sun">
                <span class="h-px w-6 bg-sun"></span>Visi
            </p>
            <p class="mt-4 font-display text-2xl leading-snug font-extrabold">
                {profile.vision ??
                    'Terwujudnya lulusan yang berakhlak Qur’ani, cakap berpikir, dan peduli lingkungan.'}
            </p>
            {#if profile.vision_note}
                <p class="mt-6 text-sm leading-relaxed text-white/80">
                    {profile.vision_note}
                </p>
            {/if}
        </div>
        <div
            use:reveal={{ delay: 120, y: 20 }}
            class="rounded-2xl bg-white p-8 shadow-lg shadow-ink/5 lg:col-span-3"
        >
            <p class="eyebrow">
                <span class="h-px w-6 bg-sun"></span>Misi
            </p>
            <ul class="mt-5 space-y-4">
                {#each missionList as item (item)}
                    <li class="flex gap-3 text-sm leading-relaxed text-ink">
                        <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-sun text-white">
                            <Check class="size-3" />
                        </span>
                        {item}
                    </li>
                {/each}
            </ul>
        </div>
    </div>
</section>

<section class="bg-white py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Angka sekolah"
            title="Kondisi terkini"
            accent="tahun ajaran ini"
        />
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {#each stats as stat, statIndex (stat.label)}
                <div
                    use:reveal={{ delay: statIndex * 90 }}
                    class="rounded-2xl border border-brand-soft p-6 text-center"
                >
                    <span class="mx-auto grid size-12 place-items-center rounded-xl bg-mist text-brand">
                        <Icon name={stat.icon} class="size-6" />
                    </span>
                    <p class="mt-4 font-display text-3xl font-extrabold text-ink">
                        {stat.value}
                    </p>
                    <p class="mt-1 text-sm text-ink-soft">{stat.label}</p>
                </div>
            {/each}
        </div>
    </div>
</section>
