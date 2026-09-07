<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import PageHero from '@/components/public/PageHero.svelte';
    import ProgramCard from '@/components/public/ProgramCard.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';
    import type { ProgramCard as ProgramCardData } from '@/types/content';

    let { programs = [] }: { programs?: ProgramCardData[] } = $props();
</script>

<AppHead title="Program" />
<PageHero
    title="Program Sekolah"
    crumb="Program"
    image="https://picsum.photos/seed/dh-prog-hero/1600/500"
/>

<section class="bg-white py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Program kami"
            title="Enam jalur pembinaan,"
            accent="satu tujuan"
            text="Setiap program punya pembina tetap, jadwal mingguan, dan indikator capaian yang dilaporkan ke orang tua."
        />
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {#each programs as program, programIndex (program.slug)}
                <ProgramCard {program} delay={(programIndex % 3) * 90} />
            {/each}
        </div>
    </div>
</section>
