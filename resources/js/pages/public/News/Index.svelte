<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import NewsCard from '@/components/public/NewsCard.svelte';
    import PageHero from '@/components/public/PageHero.svelte';
    import type { NewsItem } from '@/types/content';

    let {
        news = [],
        categories = [],
    }: {
        news?: NewsItem[];
        categories?: string[];
    } = $props();

    let active = $state('SEMUA');

    const visible = $derived(
        active === 'SEMUA'
            ? news
            : news.filter((item) => item.category === active),
    );
</script>

<AppHead title="Berita" />
<PageHero
    title="Berita & Pengumuman"
    crumb="Berita"
    image="https://picsum.photos/seed/dh-news-hero/1600/500"
/>

<section class="bg-white py-16">
    <div class="shell">
        <div use:reveal={{ y: 12 }} class="flex flex-wrap gap-2" role="group" aria-label="Filter kategori">
            {#each categories as category (category)}
                <button
                    type="button"
                    onclick={() => (active = category)}
                    aria-pressed={active === category}
                    class="rounded-full px-4 py-2 font-display text-xs font-bold tracking-wide transition
                        {active === category
                        ? 'bg-brand text-white'
                        : 'bg-mist text-ink hover:bg-brand-soft'}"
                >
                    {category}
                </button>
            {/each}
        </div>

        {#if visible.length}
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {#each visible as item, itemIndex (item.slug)}
                    <NewsCard {item} delay={(itemIndex % 3) * 90} />
                {/each}
            </div>
        {:else}
            <p class="mt-10 rounded-2xl bg-mist p-10 text-center text-sm text-ink-soft">
                Belum ada berita pada kategori ini. Pilih kategori lain.
            </p>
        {/if}
    </div>
</section>
