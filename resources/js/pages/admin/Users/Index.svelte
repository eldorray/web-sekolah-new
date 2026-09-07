<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Pengguna', href: '/admin/users' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import KeyRound from '@lucide/svelte/icons/key-round';
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
    import UserController from '@/actions/App/Http/Controllers/Admin/UserController';

    type Row = {
        id: number;
        name: string;
        email: string;
        role: string;
        is_active: boolean;
    };

    let { users = [] }: { users?: Row[] } = $props();

    let pendingDelete = $state<number | null>(null);

    const target = $derived(users.find((row) => row.id === pendingDelete) ?? null);

    const columns = [
        { label: 'Nama' },
        { label: 'Email' },
        { label: 'Peran' },
        { label: 'Akun' },
        { label: 'Aksi', align: 'right' as const },
    ];
</script>

<AppHead title="Pengguna" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Pengguna"
            description="Akun admin dan guru. Dibuat manual, tanpa pendaftaran mandiri."
        />
        <Button asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/users/create" class={props.class}>
                    <Plus class="size-4" />
                    Tambah pengguna
                </Link>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <DataTable {columns} isEmpty={users.length === 0} minWidth="46rem">
            {#snippet rows()}
                {#each users as row (row.id)}
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4 font-medium">{row.name}</td>
                        <td class="py-3 pr-4 text-muted-foreground">{row.email}</td>
                        <td class="py-3 pr-4">
                            <Badge variant={row.role === 'admin' ? 'default' : 'secondary'}>
                                {row.role === 'admin' ? 'Admin' : 'Guru'}
                            </Badge>
                        </td>
                        <td class="py-3 pr-4 text-muted-foreground">
                            {row.is_active ? 'Aktif' : 'Nonaktif'}
                        </td>
                        <td class="py-3">
                            <div class="flex justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    onclick={() =>
                                        router.post(
                                            UserController.sendPasswordReset.url({
                                                user: row.id,
                                            }),
                                            {},
                                            { preserveScroll: true },
                                        )}
                                >
                                    <KeyRound class="size-4" />
                                    <span class="sr-only">Reset password {row.name}</span>
                                </Button>
                                <Button variant="ghost" size="icon" asChild>
                                    {#snippet children(props)}
                                        <Link
                                            {...props}
                                            href={`/admin/users/${row.id}/edit`}
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
            {users.length} akun terdaftar. Pengaturan 2FA ada di halaman
            keamanan masing-masing akun.
        </p>
    </Card>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus akun ini?"
    description={`Akun ${target?.email} kehilangan akses panel. Berita miliknya tetap tersimpan.`}
    confirmLabel="Hapus akun"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(UserController.destroy.url({ user: target.id }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
