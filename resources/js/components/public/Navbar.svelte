<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import Menu from '@lucide/svelte/icons/menu';
    import X from '@lucide/svelte/icons/x';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { nav } from '@/lib/site';

    const school = $derived(page.props.school ?? {});
    const logo = $derived(page.props.branding?.logo ?? null);
    const url = currentUrlState();
    let open = $state(false);
    let scrolled = $state(false);

    /** Lifts the header once the page moves, so it separates from the hero. */
    function onScroll(): void {
        scrolled = window.scrollY > 12;
    }
</script>

<svelte:window onscroll={onScroll} />

<header
    class="sticky top-0 z-40 bg-white transition-shadow duration-300 {scrolled
        ? 'shadow-lg shadow-ink/10'
        : 'shadow-sm shadow-ink/5'}"
>
    <div
        class="shell flex items-center justify-between gap-4 transition-[height] duration-300 {scrolled
            ? 'h-16'
            : 'h-18'}"
    >
        <Link href="/" class="flex items-center gap-3" onclick={() => (open = false)}>
            {#if logo}
                <img
                    src={logo}
                    alt="Logo {school.school_name ?? 'sekolah'}"
                    class="w-auto object-contain transition-all duration-300 {scrolled
                        ? 'h-9'
                        : 'h-11'}"
                />
            {:else}
                <span
                    class="grid place-items-center rounded-xl bg-brand text-white transition-all duration-300 {scrolled
                        ? 'size-9'
                        : 'size-11'}"
                >
                    <GraduationCap class="size-6" />
                </span>
            {/if}
            <span class="leading-tight">
                <span class="block font-display text-lg font-extrabold text-ink">
                    {school.school_name ?? 'Sekolah'}
                </span>
                <span class="block text-[11px] font-medium text-ink-soft">
                    {school.foundation_name ?? ''}
                </span>
            </span>
        </Link>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Menu utama">
            {#each nav as item (item.href)}
                {@const active =
                    item.href === '/'
                        ? url.currentUrl === '/'
                        : url.isCurrentOrParentUrl(item.href.split('/').slice(0, 2).join('/'), url.currentUrl)}
                <Link
                    href={item.href}
                    aria-current={active ? 'page' : undefined}
                    class="rounded-full px-4 py-2 font-display text-sm font-semibold transition
                        {active
                        ? 'bg-mist text-brand'
                        : 'text-ink hover:bg-mist hover:text-brand'}"
                >
                    {item.label}
                </Link>
            {/each}
        </nav>

        <div class="flex items-center gap-2">
            <Link href="/ppdb" class="btn-sun hidden !py-2.5 sm:inline-flex">
                Daftar PPDB
            </Link>
            <button
                type="button"
                class="grid size-10 place-items-center rounded-xl border border-brand-soft text-brand lg:hidden"
                aria-expanded={open}
                aria-controls="mobile-nav"
                onclick={() => (open = !open)}
            >
                <span class="sr-only">{open ? 'Tutup menu' : 'Buka menu'}</span>
                {#if open}
                    <X class="size-5" />
                {:else}
                    <Menu class="size-5" />
                {/if}
            </button>
        </div>
    </div>

    {#if open}
        <nav
            id="mobile-nav"
            class="border-t border-brand-soft bg-white lg:hidden"
            aria-label="Menu utama seluler"
        >
            <ul class="shell flex flex-col py-3">
                {#each nav as item (item.href)}
                    <li>
                        <Link
                            href={item.href}
                            class="block border-b border-mist py-3 font-display text-sm font-semibold text-ink"
                            onclick={() => (open = false)}
                        >
                            {item.label}
                        </Link>
                    </li>
                {/each}
                <li class="pt-4">
                    <Link href="/ppdb" class="btn-sun w-full justify-center" onclick={() => (open = false)}>
                        Daftar PPDB
                    </Link>
                </li>
            </ul>
        </nav>
    {/if}
</header>
