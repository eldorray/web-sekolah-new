<script lang="ts">
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import X from '@lucide/svelte/icons/x';
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import PageHero from '@/components/public/PageHero.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';
    import type { GalleryPhoto } from '@/types/content';

    type Album = {
        title: string;
        description: string | null;
        photos: GalleryPhoto[];
    };

    let { album }: { album: Album } = $props();

    let openIndex = $state<number | null>(null);
    const current = $derived(
        openIndex === null ? null : (album.photos[openIndex] ?? null),
    );

    function step(delta: number): void {
        if (openIndex === null) {
            return;
        }

        openIndex =
            (openIndex + delta + album.photos.length) % album.photos.length;
    }

    function onKeydown(event: KeyboardEvent): void {
        if (openIndex === null) {
            return;
        }

        if (event.key === 'Escape') {
            openIndex = null;
        } else if (event.key === 'ArrowRight') {
            step(1);
        } else if (event.key === 'ArrowLeft') {
            step(-1);
        }
    }
</script>

<svelte:window onkeydown={onKeydown} />

<AppHead title={album.title} />
<PageHero title="Galeri" crumb={album.title} image={album.photos[0]?.image} />

<section class="bg-white py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Album"
            title={album.title}
            text={album.description ?? ''}
        />
        <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            {#each album.photos as photo, index (photo.id)}
                <button
                    use:reveal={{ delay: (index % 4) * 70, y: 12 }}
                    type="button"
                    onclick={() => (openIndex = index)}
                    class="group relative aspect-4/3 overflow-hidden rounded-xl focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    <img
                        src={photo.thumbnail}
                        alt={photo.caption ?? ''}
                        loading="lazy"
                        class="size-full object-cover transition duration-500 group-hover:scale-105"
                    />
                    <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-brand-deep/90 to-transparent p-3 text-left font-display text-xs font-semibold text-white">
                        {photo.caption}
                    </span>
                </button>
            {/each}
        </div>
    </div>
</section>

{#if current}
    <div
        class="fixed inset-0 z-50 grid place-items-center bg-brand-deep/95 p-4"
        role="dialog"
        aria-modal="true"
        aria-label={current.caption ?? 'Foto galeri'}
    >
        <button
            type="button"
            onclick={() => (openIndex = null)}
            class="absolute top-5 right-5 grid size-11 place-items-center rounded-full bg-white/15 text-white hover:bg-sun"
        >
            <span class="sr-only">Tutup galeri</span>
            <X class="size-5" />
        </button>
        <button
            type="button"
            onclick={() => step(-1)}
            class="absolute left-4 grid size-11 place-items-center rounded-full bg-white/15 text-white hover:bg-sun"
        >
            <span class="sr-only">Foto sebelumnya</span>
            <ChevronLeft class="size-5" />
        </button>
        <figure class="max-h-full max-w-4xl">
            <img
                src={current.image ?? current.thumbnail}
                alt={current.caption ?? ''}
                class="max-h-[75vh] w-full rounded-2xl object-contain"
            />
            <figcaption class="mt-4 text-center font-display text-sm font-semibold text-white">
                {current.caption}
                <span class="ml-2 text-white/60">
                    {(openIndex ?? 0) + 1} / {album.photos.length}
                </span>
            </figcaption>
        </figure>
        <button
            type="button"
            onclick={() => step(1)}
            class="absolute right-4 grid size-11 place-items-center rounded-full bg-white/15 text-white hover:bg-sun"
        >
            <span class="sr-only">Foto berikutnya</span>
            <ChevronRight class="size-5" />
        </button>
    </div>
{/if}
