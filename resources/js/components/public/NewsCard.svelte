<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import UserRound from '@lucide/svelte/icons/user-round';
    import { reveal } from '@/lib/reveal';
    import type { NewsItem } from '@/types/content';

    let {
        item,
        delay = 0,
    }: {
        item: NewsItem;
        delay?: number;
    } = $props();
</script>

<!-- Reveal sits on the wrapper so the card's own hover transition stays intact. -->
<div use:reveal={{ delay }} class="flex">
    <article
            class="group flex w-full flex-col overflow-hidden rounded-2xl bg-white shadow-lg shadow-ink/5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-ink/10"
    >
        <Link href={`/berita/${item.slug}`} class="relative block aspect-16/10 overflow-hidden">
            <img
                src={item.image ?? ''}
                alt={item.title}
                loading="lazy"
                class="size-full object-cover transition duration-500 group-hover:scale-105"
            />
            <span class="absolute top-4 left-4 rounded-full bg-brand px-3 py-1 font-display text-[11px] font-bold tracking-wide text-white">
                {item.category}
            </span>
        </Link>
        <div class="flex flex-1 flex-col p-6">
            <div class="flex flex-wrap items-center gap-4 text-xs text-ink-soft">
                <span class="inline-flex items-center gap-1.5">
                    <CalendarDays class="size-3.5 text-sun" />{item.date}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <UserRound class="size-3.5 text-sun" />{item.author}
                </span>
            </div>
            <h3 class="mt-3 font-display text-lg leading-snug font-bold text-ink">
                <Link href={`/berita/${item.slug}`} class="hover:text-brand">
                    {item.title}
                </Link>
            </h3>
            <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-soft">
                {item.excerpt}
            </p>
        </div>
    </article>
</div>
