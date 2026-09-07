<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Galeri', href: '/admin/gallery' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Images from '@lucide/svelte/icons/images';
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
    import GalleryController from '@/actions/App/Http/Controllers/Admin/GalleryController';

    type Row = {
        slug: string;
        title: string;
        photos: number;
        order: number;
        is_published: boolean;
    };

    let { albums = [] }: { albums?: Row[] } = $props();

    let pendingDelete = $state<string | null>(null);

    const target = $derived(
        albums.find((row) => row.slug === pendingDelete) ?? null,
    );

    const columns = [
        { label: 'Album' },
        { label: 'Foto' },
        { label: 'Urutan' },
        { label: 'Status' },
        { label: 'Aksi', align: 'right' as const },
    ];
</script>

<AppHead title="Galeri" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Galeri"
            description="Album foto kegiatan. Foto dikelola per album."
        />
        <Button asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/gallery/create" class={props.class}>
                    <Plus class="size-4" />
                    Tambah album
                </Link>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <DataTable {columns} isEmpty={albums.length === 0} minWidth="42rem">
            {#snippet rows()}
                {#each albums as row (row.slug)}
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4 font-medium">{row.title}</td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">
                            {row.photos} foto
                        </td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">{row.order}</td>
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
                                            href={`/admin/gallery/${row.slug}/photos`}
                                            class={props.class}
                                        >
                                            <Images class="size-4" />
                                            <span class="sr-only">Kelola foto {row.title}</span>
                                        </Link>
                                    {/snippet}
                                </Button>
                                <Button variant="ghost" size="icon" asChild>
                                    {#snippet children(props)}
                                        <Link
                                            {...props}
                                            href={`/admin/gallery/${row.slug}/edit`}
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
    title="Hapus album ini?"
    description={`"${target?.title}" dan ${target?.photos ?? 0} fotonya akan dihapus.`}
    confirmLabel="Hapus album"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(GalleryController.destroy.url({ album: target.slug }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
