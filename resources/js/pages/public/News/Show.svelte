<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import UserRound from '@lucide/svelte/icons/user-round';
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import PageHero from '@/components/public/PageHero.svelte';
    import type { NewsItem } from '@/types/content';

    type NewsDetail = NewsItem & { content: string | null };

    let {
        item,
        related = [],
    }: {
        item: NewsDetail;
        related?: NewsItem[];
    } = $props();
</script>

<AppHead title={item.title} />
<PageHero title={item.category} crumb="Berita" image={item.image ?? undefined} />

<section class="bg-white py-16">
    <div class="shell grid gap-12 lg:grid-cols-3">
        <article class="lg:col-span-2">
            <img
                use:reveal={{ y: 20 }}
                src={item.image ?? ''}
                alt={item.title}
                class="aspect-16/9 w-full rounded-2xl object-cover"
            />
            <div class="mt-6 flex flex-wrap items-center gap-5 text-xs text-ink-soft">
                <span class="rounded-full bg-brand px-3 py-1 font-display font-bold text-white">
                    {item.category}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <CalendarDays class="size-4 text-sun" />{item.date}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <UserRound class="size-4 text-sun" />{item.author}
                </span>
            </div>
            <h1
                use:reveal={{ delay: 100 }}
                class="mt-4 font-display text-2xl leading-snug font-extrabold text-ink sm:text-4xl"
            >
                {item.title}
            </h1>

            <p class="mt-6 text-base leading-relaxed text-ink">{item.excerpt}</p>

            <!-- Sanitized on save by HtmlSanitizer; never raw user input. -->
            <div
                use:reveal={{ delay: 160 }}
                class="prose-content mt-4 text-sm leading-relaxed text-ink-soft"
            >
                {@html item.content ?? ''}
            </div>

            <Link
                href="/berita"
                class="mt-8 inline-flex items-center gap-2 font-display text-sm font-bold text-brand hover:text-sun"
            >
                <ArrowLeft class="size-4" />
                Kembali ke daftar berita
            </Link>
        </article>

        <aside>
            <div use:reveal={{ delay: 120 }} class="rounded-2xl bg-mist p-6">
                <p class="font-display text-sm font-bold text-ink uppercase">
                    Berita lain
                </p>
                <ul class="mt-4 space-y-3">
                    {#each related as entry (entry.slug)}
                        <li>
                            <Link
                                href={`/berita/${entry.slug}`}
                                class="flex gap-3 rounded-xl bg-white p-3 transition hover:bg-brand hover:text-white"
                            >
                                <img
                                    src={entry.image ?? ''}
                                    alt=""
                                    loading="lazy"
                                    class="size-14 shrink-0 rounded-lg object-cover"
                                />
                                <span>
                                    <span class="block font-display text-sm leading-snug font-semibold">
                                        {entry.title}
                                    </span>
                                    <span class="mt-1 block text-xs opacity-70">
                                        {entry.date}
                                    </span>
                                </span>
                            </Link>
                        </li>
                    {/each}
                </ul>
            </div>
        </aside>
    </div>
</section>
