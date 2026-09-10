<script lang="ts">
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import Play from '@lucide/svelte/icons/play';
    import X from '@lucide/svelte/icons/x';
    import SectionHead from '@/components/public/SectionHead.svelte';
    import { reveal } from '@/lib/reveal';

    export type Video = {
        id: number;
        title: string;
        description: string | null;
        thumbnail: string;
        embed_url: string;
    };

    let { videos = [] }: { videos?: Video[] } = $props();

    /**
     * ponytail: a native scroll container with scroll-snap instead of a
     * carousel library — swipe and trackpad work for free. Ceiling: the loop
     * jumps back to the start rather than wrapping seamlessly; a duplicated
     * track would be needed for that, and it is not worth the complexity here.
     */
    let track = $state<HTMLDivElement | null>(null);
    let playing = $state<Video | null>(null);
    let paused = $state(false);

    const reduceMotion = (): boolean =>
        typeof window !== 'undefined' &&
        window.matchMedia?.('(prefers-reduced-motion: reduce)').matches === true;

    function step(): number {
        const first = track?.firstElementChild as HTMLElement | null;

        return first ? first.offsetWidth + 24 : 320;
    }

    function slide(direction: 1 | -1): void {
        if (track === null) {
            return;
        }

        const atEnd =
            track.scrollLeft + track.clientWidth >= track.scrollWidth - 8;
        const atStart = track.scrollLeft <= 8;

        // Wrap around so the row keeps going in both directions.
        if (direction === 1 && atEnd) {
            track.scrollTo({ left: 0, behavior: 'smooth' });

            return;
        }

        if (direction === -1 && atStart) {
            track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });

            return;
        }

        track.scrollBy({ left: direction * step(), behavior: 'smooth' });
    }

    // Keeps rolling on its own; stops while hovered, focused, or when a video
    // is open, and never starts for readers who asked for reduced motion.
    $effect(() => {
        if (videos.length < 2 || reduceMotion()) {
            return;
        }

        const timer = setInterval(() => {
            if (!paused && playing === null) {
                slide(1);
            }
        }, 4500);

        return () => clearInterval(timer);
    });
</script>

{#if videos.length > 0}
    <section class="bg-white py-20">
        <div class="shell">
            <SectionHead
                eyebrow="Galeri video"
                title="Lihat kegiatan kami"
                accent="dalam video"
                text="Rekaman kegiatan, prestasi, dan projek siswa. Geser ke samping untuk melihat lebih banyak."
            />

            <div
                bind:this={track}
                role="list"
                aria-label="Galeri video"
                onmouseenter={() => (paused = true)}
                onmouseleave={() => (paused = false)}
                onfocusin={() => (paused = true)}
                onfocusout={() => (paused = false)}
                class="mt-10 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            >
                {#each videos as video, index (video.id)}
                    <div
                        role="listitem"
                        use:reveal={{ delay: Math.min(index, 3) * 90 }}
                        class="w-[17rem] shrink-0 snap-start sm:w-[19rem] lg:w-[calc((100%-4.5rem)/4)]"
                    >
                        <button
                            type="button"
                            onclick={() => (playing = video)}
                            class="group w-full text-left"
                        >
                            <span
                                class="relative block aspect-video overflow-hidden rounded-2xl bg-mist shadow-lg shadow-ink/5"
                            >
                                <img
                                    src={video.thumbnail}
                                    alt={video.title}
                                    loading="lazy"
                                    class="size-full object-cover transition duration-500 group-hover:scale-105"
                                />
                                <span
                                    class="absolute inset-0 grid place-items-center bg-brand-deep/35 transition group-hover:bg-brand-deep/55"
                                >
                                    <span
                                        class="grid size-14 place-items-center rounded-full bg-white/95 text-brand shadow-lg transition group-hover:scale-110"
                                    >
                                        <Play class="size-6 translate-x-0.5" />
                                    </span>
                                </span>
                            </span>
                            <span class="mt-4 block font-display text-base font-bold text-ink group-hover:text-brand">
                                {video.title}
                            </span>
                            {#if video.description}
                                <span class="mt-1 block text-sm leading-relaxed text-ink-soft">
                                    {video.description}
                                </span>
                            {/if}
                        </button>
                    </div>
                {/each}
            </div>

            {#if videos.length > 1}
                <div use:reveal class="mt-6 flex justify-center gap-2">
                    <button
                        type="button"
                        onclick={() => slide(-1)}
                        class="grid size-11 place-items-center rounded-full border border-brand-soft text-brand transition hover:bg-brand hover:text-white focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <span class="sr-only">Video sebelumnya</span>
                        <ChevronLeft class="size-5" />
                    </button>
                    <button
                        type="button"
                        onclick={() => slide(1)}
                        class="grid size-11 place-items-center rounded-full border border-brand-soft text-brand transition hover:bg-brand hover:text-white focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <span class="sr-only">Video berikutnya</span>
                        <ChevronRight class="size-5" />
                    </button>
                </div>
            {/if}
        </div>
    </section>
{/if}

{#if playing}
    <div
        class="fixed inset-0 z-50 grid place-items-center bg-brand-deep/95 p-4"
        role="dialog"
        aria-modal="true"
        aria-label={playing.title}
    >
        <button
            type="button"
            onclick={() => (playing = null)}
            class="absolute top-5 right-5 grid size-11 place-items-center rounded-full bg-white/15 text-white transition hover:bg-sun"
        >
            <span class="sr-only">Tutup video</span>
            <X class="size-5" />
        </button>
        <figure class="w-full max-w-4xl">
            <div class="aspect-video w-full overflow-hidden rounded-2xl bg-black">
                <iframe
                    src={playing.embed_url}
                    title={playing.title}
                    class="size-full"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen
                ></iframe>
            </div>
            <figcaption class="mt-4 text-center font-display text-sm font-semibold text-white">
                {playing.title}
            </figcaption>
        </figure>
    </div>
{/if}

<svelte:window
    onkeydown={(event) => {
        if (event.key === 'Escape') {
            playing = null;
        }
    }}
/>
