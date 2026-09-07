<script lang="ts">
    import { Link } from '@inertiajs/svelte';

    /** Shape of Laravel's paginator links, so wiring the real paginator is a prop swap. */
    export type PaginationLink = {
        url: string | null;
        label: string;
        active: boolean;
    };

    let {
        links = [],
        from = 0,
        to = 0,
        total = 0,
    }: {
        links?: PaginationLink[];
        from?: number;
        to?: number;
        total?: number;
    } = $props();

    /** Paginator labels arrive HTML-escaped; render them as text, never as markup. */
    function label(raw: string): string {
        return raw
            .replaceAll('&laquo;', '\u2039')
            .replaceAll('&raquo;', '\u203a')
            .replaceAll('Previous', 'Sebelumnya')
            .replaceAll('Next', 'Berikutnya')
            .trim();
    }
</script>

<div class="flex flex-wrap items-center justify-between gap-3 px-5">
    <p class="text-xs text-muted-foreground">
        Menampilkan {from}–{to} dari {total} data.
    </p>

    {#if links.length > 3}
        <nav class="flex flex-wrap gap-1" aria-label="Navigasi halaman">
            {#each links as link (link.label)}
                {#if link.url === null}
                    <span
                        class="rounded-md px-3 py-1.5 text-sm text-muted-foreground/50"
                    >
                        {label(link.label)}
                    </span>
                {:else}
                    <Link
                        href={link.url}
                        aria-current={link.active ? 'page' : undefined}
                        class="rounded-md px-3 py-1.5 text-sm transition {link.active
                            ? 'bg-brand text-white'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'}"
                    >
                        {label(link.label)}
                    </Link>
                {/if}
            {/each}
        </nav>
    {/if}
</div>
