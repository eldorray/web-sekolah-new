<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import Icon from '@/components/public/Icon.svelte';
    import { reveal } from '@/lib/reveal';
    import type { ProgramCard } from '@/types/content';

    let {
        program,
        delay = 0,
    }: {
        program: ProgramCard;
        delay?: number;
    } = $props();
</script>

<!-- Reveal sits on the wrapper so the card's own hover transition stays intact. -->
<div use:reveal={{ delay }} class="flex">
    <article
            class="group flex w-full flex-col overflow-hidden rounded-2xl bg-white shadow-lg shadow-ink/5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-ink/10"
    >
        <div class="relative aspect-16/10 overflow-hidden">
            <img
                src={program.image ?? ''}
                alt={program.title}
                loading="lazy"
                class="size-full object-cover transition duration-500 group-hover:scale-105"
            />
            <span class="absolute top-4 left-4 rounded-full bg-sun px-3 py-1 font-display text-[11px] font-bold text-white">
                {program.badge}
            </span>
        </div>
        <div class="flex flex-1 flex-col p-6">
            <span class="grid size-11 place-items-center rounded-xl bg-mist text-brand">
                <Icon name={program.icon} class="size-5" />
            </span>
            <h3 class="mt-4 font-display text-lg font-bold text-ink">
                <Link href={`/program/${program.slug}`} class="hover:text-brand">
                    {program.title}
                </Link>
            </h3>
            <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-soft">
                {program.excerpt}
            </p>
            <Link
                href={`/program/${program.slug}`}
                class="mt-5 inline-flex items-center gap-2 font-display text-sm font-bold text-brand hover:text-sun"
            >
                Lihat program
                <ArrowRight class="size-4" />
            </Link>
        </div>
    </article>
</div>
