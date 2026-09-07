<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Guru', href: '/guru' },
            { title: 'Berita saya', href: '/guru/news' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import ConfirmDelete from '@/components/admin/ConfirmDelete.svelte';
    import DataTable from '@/components/admin/DataTable.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import NewsController from '@/actions/App/Http/Controllers/Admin/NewsController';

    type Row = {
        slug: string;
        title: string;
        category: string;
        published_at: string | null;
        is_published: boolean;
    };

    let { news = [] }: { news?: Row[] } = $props();

    const mine = $derived(news);

    let pendingDelete = $state<string | null>(null);

    const target = $derived(mine.find((row) => row.slug === pendingDelete) ?? null);

    const columns = [
        { label: 'Judul' },
        { label: 'Kategori' },
        { label: 'Terbit' },
        { label: 'Status' },
        { label: 'Aksi', align: 'right' as const },
    ];
</script>

<AppHead title="Berita saya" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Berita saya"
            description="Hanya berita yang Anda tulis sendiri yang tampil di sini."
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

    <Card class="gap-4 py-5">
        <DataTable
            {columns}
            isEmpty={mine.length === 0}
            empty="Belum ada berita yang Anda tulis."
            minWidth="42rem"
        >
            {#snippet rows()}
                {#each mine as row (row.slug)}
                    <tr class="border-b last:border-0">
                        <td class="max-w-sm py-3 pr-4 font-medium">{row.title}</td>
                        <td class="py-3 pr-4 text-muted-foreground">{row.category}</td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">
                            {row.published_at}
                        </td>
                        <td class="py-3 pr-4">
                            <Badge variant={row.is_published ? 'default' : 'secondary'}>
                                {row.is_published ? 'Terbit' : 'Draf'}
                            </Badge>
                        </td>
                        <td class="py-3">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="icon" asChild>
                                    {#snippet children(props)}
                                        <Link
                                            {...props}
                                            href={`/guru/news/${row.slug}/edit`}
                                            class={props.class}
                                        >
                                            <Pencil class="size-4" />
                                            <span class="sr-only">Ubah {row.title}</span>
                                        </Link>
                                    {/snippet}
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    onclick={() => (pendingDelete = row.slug)}
                                >
                                    <Trash2 class="size-4 text-destructive" />
                                    <span class="sr-only">Hapus {row.title}</span>
                                </Button>
                            </div>
                        </td>
                    </tr>
                {/each}
            {/snippet}
        </DataTable>
    </Card>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus berita ini?"
    description={`"${target?.title}" akan dipindahkan ke tempat sampah.`}
    confirmLabel="Hapus berita"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(NewsController.destroy.url({ news: target.slug }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
