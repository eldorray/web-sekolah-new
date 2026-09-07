<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Guru', href: '/admin/teachers' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import ConfirmDelete from '@/components/admin/ConfirmDelete.svelte';
    import DataTable from '@/components/admin/DataTable.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import TeacherController from '@/actions/App/Http/Controllers/Admin/TeacherController';

    type Row = {
        id: number;
        name: string;
        email: string;
        position: string | null;
        role: string;
        is_active: boolean;
    };

    let { teachers = [] }: { teachers?: Row[] } = $props();

    let search = $state('');
    let pendingDelete = $state<number | null>(null);

    const visible = $derived(
        teachers.filter((row) =>
            `${row.name} ${row.position ?? ''}`
                .toLowerCase()
                .includes(search.trim().toLowerCase()),
        ),
    );

    const target = $derived(
        teachers.find((row) => row.id === pendingDelete) ?? null,
    );

    const columns = [
        { label: 'Nama' },
        { label: 'Jabatan' },
        { label: 'Email' },
        { label: 'Tampil publik' },
        { label: 'Aksi', align: 'right' as const },
    ];
</script>

<AppHead title="Guru" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Guru & tenaga pendidik"
            description="Data yang tampil di halaman Tim Guru situs publik."
        />
        <Button asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/teachers/create" class={props.class}>
                    <Plus class="size-4" />
                    Tambah guru
                </Link>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <div class="px-5">
            <div class="relative max-w-sm">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    bind:value={search}
                    class="pl-9"
                    placeholder="Cari nama atau jabatan"
                    aria-label="Cari guru"
                />
            </div>
        </div>

        <DataTable
            {columns}
            isEmpty={visible.length === 0}
            empty="Tidak ada guru yang cocok."
            minWidth="46rem"
        >
            {#snippet rows()}
                {#each visible as row (row.id)}
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4 font-medium">{row.name}</td>
                        <td class="py-3 pr-4 text-muted-foreground">{row.position}</td>
                        <td class="py-3 pr-4 text-muted-foreground">{row.email}</td>
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
                                            href={`/admin/teachers/${row.id}/edit`}
                                            class={props.class}
                                        >
                                            <Pencil class="size-4" />
                                            <span class="sr-only">Ubah {row.name}</span>
                                        </Link>
                                    {/snippet}
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    onclick={() => (pendingDelete = row.id)}
                                >
                                    <Trash2 class="size-4 text-destructive" />
                                    <span class="sr-only">Hapus {row.name}</span>
                                </Button>
                            </div>
                        </td>
                    </tr>
                {/each}
            {/snippet}
        </DataTable>

        <p class="px-5 text-xs text-muted-foreground">
            Menampilkan {visible.length} dari {teachers.length} guru.
        </p>
    </Card>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus data guru ini?"
    description={`"${target?.name}" akan hilang dari halaman Tim Guru.`}
    confirmLabel="Hapus guru"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(TeacherController.destroy.url({ teacher: target.id }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
