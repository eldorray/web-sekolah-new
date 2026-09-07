<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import { reveal } from '@/lib/reveal';

    let {
        title,
        crumb,
        image = '',
    }: { title: string; crumb: string; image?: string } = $props();
</script>

<section class="relative isolate overflow-hidden bg-brand-deep">
    {#if image}
        <img
            src={image}
            alt=""
            class="absolute inset-0 size-full object-cover opacity-35"
        />
    {/if}
    <div class="absolute inset-0 bg-gradient-to-r from-brand-deep via-brand-deep/85 to-brand/50"></div>
    <div class="shell relative py-16 sm:py-20">
        <h1
            use:reveal={{ y: 20 }}
            class="font-display text-3xl font-extrabold text-white sm:text-5xl"
        >
            {title}
        </h1>
        <nav
            use:reveal={{ delay: 120 }}
            aria-label="Breadcrumb"
            class="mt-4 flex items-center gap-2 text-sm text-white/80"
        >
            <Link href="/" class="hover:text-sun">Beranda</Link>
            <ChevronRight class="size-4 text-sun" />
            <span class="font-semibold text-sun">{crumb}</span>
        </nav>
    </div>
</section>
