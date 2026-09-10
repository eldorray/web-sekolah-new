<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Video', href: '/admin/videos' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import VideoController from '@/actions/App/Http/Controllers/Admin/VideoController';
    import ConfirmDelete from '@/components/admin/ConfirmDelete.svelte';
    import DataTable from '@/components/admin/DataTable.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';

    type Row = {
        id: number;
        title: string;
        youtube_id: string;
        thumbnail: string;
        watch_url: string;
        order: number;
        is_active: boolean;
    };

    let { videos = [] }: { videos?: Row[] } = $props();

    let pendingDelete = $state<number | null>(null);

    const target = $derived(videos.find((row) => row.id === pendingDelete) ?? null);

    const columns = [
        { label: 'Video' },
        { label: 'ID YouTube' },
        { label: 'Urutan' },
        { label: 'Status' },
        { label: 'Aksi', align: 'right' as const },
    ];
</script>

<AppHead title="Video" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Galeri video"
            description="Video YouTube yang tampil di beranda, di atas seksi berita."
        />
        <Button asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/videos/create" class={props.class}>
                    <Plus class="size-4" />
                    Tambah video
                </Link>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <DataTable
            {columns}
            isEmpty={videos.length === 0}
            empty="Belum ada video. Tambahkan lewat tombol di atas."
            minWidth="44rem"
        >
            {#snippet rows()}
                {#each videos as row (row.id)}
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3">
                                <img
                                    src={row.thumbnail}
                                    alt=""
                                    loading="lazy"
                                    class="h-12 w-20 shrink-0 rounded object-cover"
                                />
                                <span class="font-medium">{row.title}</span>
                            </div>
                        </td>
                        <td class="py-3 pr-4">
                            <a
                                href={row.watch_url}
                                target="_blank"
                                rel="noreferrer"
                                class="inline-flex items-center gap-1.5 font-mono text-xs text-brand hover:underline"
                            >
                                {row.youtube_id}
                                <ExternalLink class="size-3" />
                            </a>
                        </td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">
                            {row.order}
                        </td>
                        <td class="py-3 pr-4">
                            <Badge variant={row.is_active ? 'default' : 'secondary'}>
                                {row.is_active ? 'Tampil' : 'Disembunyikan'}
                            </Badge>
                        </td>
                        <td class="py-3">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="icon" asChild>
                                    {#snippet children(props)}
                                        <Link
                                            {...props}
                                            href={`/admin/videos/${row.id}/edit`}
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
                                    onclick={() => (pendingDelete = row.id)}
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

        <p class="px-5 text-xs text-muted-foreground">
            {videos.length} video terdaftar. Beranda menampilkan empat sekaligus dan
            sisanya bisa digeser.
        </p>
    </Card>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus video ini?"
    description={`"${target?.title}" akan hilang dari beranda. Videonya di YouTube tidak terpengaruh.`}
    confirmLabel="Hapus video"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(VideoController.destroy.url({ video: target.id }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
