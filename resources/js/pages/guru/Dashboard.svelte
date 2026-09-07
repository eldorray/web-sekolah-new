<script module lang="ts">
    export const layout = {
        breadcrumbs: [{ title: 'Panel Guru', href: '/guru' }],
    };
</script>

<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import Plus from '@lucide/svelte/icons/plus';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    type Tile = { key: string; label: string; value: string; hint: string };
    type Row = {
        slug: string;
        title: string;
        category: string;
        published_at: string | null;
        is_published: boolean;
    };

    let {
        summary = [],
        news = [],
    }: {
        summary?: Tile[];
        news?: Row[];
    } = $props();

    const auth = $derived(page.props.auth);
</script>

<AppHead title="Panel Guru" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={`Selamat datang, ${auth.user?.name ?? 'Guru'}`}
            description="Ringkasan berita yang Anda tulis dan pintasan tugas harian."
        />
        <Button asChild>
            {#snippet children(props)}
                <Link {...props} href="/guru/news/create" class={props.class}>
                    <Plus class="size-4" />
                    Tulis berita
                </Link>
            {/snippet}
        </Button>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        {#each summary as tile (tile.key)}
            <Card class="gap-2 py-5">
                <div class="px-5">
                    <p class="text-sm text-muted-foreground">{tile.label}</p>
                    <p class="mt-2 font-display text-3xl font-extrabold tracking-tight">
                        {tile.value}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">{tile.hint}</p>
                </div>
            </Card>
        {/each}
    </div>

    <Card class="gap-4 py-5">
        <div class="flex items-center justify-between px-5">
            <h3 class="text-base font-medium">Berita terbaru saya</h3>
            <Link
                href="/guru/news"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-brand hover:underline"
            >
                Semua berita saya
                <ArrowRight class="size-4" />
            </Link>
        </div>
        {#if news.length === 0}
            <p class="px-5 pb-2 text-sm text-muted-foreground">
                Belum ada berita. Mulai dengan tombol Tulis berita.
            </p>
        {/if}
        <ul class="divide-y px-5">
            {#each news as row (row.slug)}
                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div>
                        <p class="text-sm font-medium">{row.title}</p>
                        <p class="text-xs text-muted-foreground">
                            {row.category} · {row.published_at}
                        </p>
                    </div>
                    <Badge variant={row.is_published ? 'default' : 'secondary'}>
                        {row.is_published ? 'Terbit' : 'Draf'}
                    </Badge>
                </li>
            {/each}
        </ul>
    </Card>
</div>
