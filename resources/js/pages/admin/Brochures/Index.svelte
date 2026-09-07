<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Brosur', href: '/admin/brochures' },
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
    import BrochureController from '@/actions/App/Http/Controllers/Admin/BrochureController';

    type Row = {
        id: number;
        title: string;
        subtitle: string | null;
        pages: number;
        has_file: boolean;
        order: number;
        is_active: boolean;
    };

    let { brochures = [] }: { brochures?: Row[] } = $props();

    let pendingDelete = $state<number | null>(null);

    const target = $derived(
        brochures.find((row) => row.id === pendingDelete) ?? null,
    );

    const columns = [
        { label: 'Judul' },
        { label: 'Halaman gambar' },
        { label: 'Berkas PDF' },
        { label: 'Urutan' },
        { label: 'Status' },
        { label: 'Aksi', align: 'right' as const },
    ];
</script>

<AppHead title="Brosur" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Brosur"
            description="Brosur PPDB dan profil sekolah yang bisa diunduh pengunjung."
        />
        <Button asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/brochures/create" class={props.class}>
                    <Plus class="size-4" />
                    Tambah brosur
                </Link>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <DataTable {columns} isEmpty={brochures.length === 0} minWidth="46rem">
            {#snippet rows()}
                {#each brochures as row (row.id)}
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4">
                            <p class="font-medium">{row.title}</p>
                            <p class="text-xs text-muted-foreground">{row.subtitle}</p>
                        </td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">
                            {row.pages} halaman
                        </td>
                        <td class="py-3 pr-4 text-muted-foreground">
                            {row.has_file ? 'Ada' : 'Belum diunggah'}
                        </td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">{row.order}</td>
                        <td class="py-3 pr-4">
                            <Badge variant={row.is_active ? 'default' : 'secondary'}>
                                {row.is_active ? 'Aktif' : 'Nonaktif'}
                            </Badge>
                        </td>
                        <td class="py-3">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="icon" asChild>
                                    {#snippet children(props)}
                                        <Link
                                            {...props}
                                            href={`/admin/brochures/${row.id}/edit`}
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
    </Card>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus brosur ini?"
    description={`"${target?.title}" beserta halaman gambarnya akan dihapus.`}
    confirmLabel="Hapus brosur"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(BrochureController.destroy.url({ brochure: target.id }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
