<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Program', href: '/admin/programs' },
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
    import Icon from '@/components/public/Icon.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import ProgramController from '@/actions/App/Http/Controllers/Admin/ProgramController';

    type Row = {
        slug: string;
        title: string;
        icon: string;
        order: number;
        is_active: boolean;
    };

    let { programs = [] }: { programs?: Row[] } = $props();

    let pendingDelete = $state<string | null>(null);

    const target = $derived(
        programs.find((row) => row.slug === pendingDelete) ?? null,
    );

    const columns = [
        { label: 'Program' },
        { label: 'Ikon' },
        { label: 'Urutan' },
        { label: 'Status' },
        { label: 'Aksi', align: 'right' as const },
    ];
</script>

<AppHead title="Program" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Program"
            description="Program unggulan yang tampil di situs publik."
        />
        <Button asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/programs/create" class={props.class}>
                    <Plus class="size-4" />
                    Tambah program
                </Link>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <DataTable {columns} isEmpty={programs.length === 0} minWidth="38rem">
            {#snippet rows()}
                {#each programs as row (row.slug)}
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4 font-medium">{row.title}</td>
                        <td class="py-3 pr-4">
                            <span class="inline-flex items-center gap-2 text-muted-foreground">
                                <Icon name={row.icon} class="size-4" />
                                {row.icon}
                            </span>
                        </td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">
                            {row.order}
                        </td>
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
                                            href={`/admin/programs/${row.slug}/edit`}
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

        <p class="px-5 text-xs text-muted-foreground">
            {programs.length} program terdaftar.
        </p>
    </Card>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus program ini?"
    description={`"${target?.title}" akan hilang dari situs publik.`}
    confirmLabel="Hapus program"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(ProgramController.destroy.url({ program: target.slug }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
