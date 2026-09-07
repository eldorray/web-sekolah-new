<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import Icon from '@/components/public/Icon.svelte';
    import PageHero from '@/components/public/PageHero.svelte';
    import type { ProgramCard } from '@/types/content';

    type ProgramDetail = ProgramCard & { description: string | null };

    let {
        program,
        others = [],
    }: {
        program: ProgramDetail;
        others?: ProgramCard[];
    } = $props();
</script>

<AppHead title={program.title} />
<PageHero title={program.title} crumb="Program" image={program.image ?? undefined} />

<section class="bg-white py-16">
    <div class="shell grid gap-12 lg:grid-cols-3">
        <article class="lg:col-span-2">
            <img
                use:reveal={{ y: 20 }}
                src={program.image ?? ''}
                alt={program.title}
                class="aspect-16/9 w-full rounded-2xl object-cover"
            />
            <div class="mt-8 flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-brand text-white">
                    <Icon name={program.icon} class="size-5" />
                </span>
                <span class="rounded-full bg-sun-soft px-3 py-1 font-display text-xs font-bold text-sun-dark">
                    {program.badge}
                </span>
            </div>
            <h2
                use:reveal={{ delay: 100 }}
                class="mt-5 font-display text-2xl font-extrabold text-ink sm:text-3xl"
            >
                {program.title}
            </h2>
            <p class="mt-4 text-base leading-relaxed text-ink">{program.excerpt}</p>

            <!-- Sanitized on save by HtmlSanitizer; never raw user input. -->
            <div
                use:reveal={{ delay: 160 }}
                class="prose-content mt-4 text-sm leading-relaxed text-ink-soft"
            >
                {@html program.description ?? ''}
            </div>
            <Link
                href="/program"
                class="mt-8 inline-flex items-center gap-2 font-display text-sm font-bold text-brand hover:text-sun"
            >
                <ArrowLeft class="size-4" />
                Kembali ke daftar program
            </Link>
        </article>

        <aside>
            <div use:reveal={{ delay: 120 }} class="rounded-2xl bg-mist p-6">
                <p class="font-display text-sm font-bold text-ink uppercase">
                    Program lain
                </p>
                <ul class="mt-4 space-y-3">
                    {#each others as item (item.slug)}
                        <li>
                            <Link
                                href={`/program/${item.slug}`}
                                class="flex items-center gap-3 rounded-xl bg-white p-3 transition hover:bg-brand hover:text-white"
                            >
                                <img
                                    src={item.image ?? ''}
                                    alt=""
                                    loading="lazy"
                                    class="size-12 shrink-0 rounded-lg object-cover"
                                />
                                <span class="font-display text-sm font-semibold">
                                    {item.title}
                                </span>
                            </Link>
                        </li>
                    {/each}
                </ul>
            </div>
            <Link href="/ppdb" class="btn-sun mt-6 w-full justify-center">
                Daftar PPDB
            </Link>
        </aside>
    </div>
</section>
