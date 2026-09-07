<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'PPDB', href: '/admin/ppdb' },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Download from '@lucide/svelte/icons/download';
    import Search from '@lucide/svelte/icons/search';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import {
        statusBadgeClass,
        statusLabels,
        type PpdbStatus,
    } from '@/lib/admin';
    import PpdbController from '@/actions/App/Http/Controllers/Admin/PpdbController';

    type Registration = {
        number: string;
        full_name: string;
        grade_target: string;
        parent_phone: string;
        created_at: string | null;
        status: PpdbStatus;
    };

    let { registrations = [] }: { registrations?: Registration[] } = $props();

    // ponytail: client-side filter over placeholder rows; swap for the
    // paginated query once PpdbRegistration exists.
    let search = $state('');
    let status = $state<'SEMUA' | PpdbStatus>('SEMUA');

    const tabs: Array<{ key: 'SEMUA' | PpdbStatus; label: string }> = [
        { key: 'SEMUA', label: 'Semua' },
        { key: 'pending', label: 'Menunggu' },
        { key: 'accepted', label: 'Diterima' },
        { key: 'rejected', label: 'Ditolak' },
    ];

    const counts = $derived({
        SEMUA: registrations.length,
        pending: registrations.filter((row) => row.status === 'pending').length,
        accepted: registrations.filter((row) => row.status === 'accepted').length,
        rejected: registrations.filter((row) => row.status === 'rejected').length,
    });

    const rows = $derived(
        registrations.filter((row) => {
            const needle = search.trim().toLowerCase();
            const matchesSearch =
                needle === '' ||
                row.full_name.toLowerCase().includes(needle) ||
                row.number.toLowerCase().includes(needle) ||
                row.parent_phone.includes(needle);

            return matchesSearch && (status === 'SEMUA' || row.status === status);
        }),
    );

</script>

<AppHead title="PPDB" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Pendaftar PPDB"
            description="Verifikasi berkas, ubah status, dan unduh rekap pendaftar."
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <a {...props} href={PpdbController.export.url()} class={props.class}>
                    <Download class="size-4" />
                    Ekspor CSV
                </a>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <div class="flex flex-wrap items-center gap-3 px-5">
            <div class="flex flex-wrap gap-1" role="group" aria-label="Filter status">
                {#each tabs as tab (tab.key)}
                    <button
                        type="button"
                        onclick={() => (status = tab.key)}
                        aria-pressed={status === tab.key}
                        class="rounded-full px-3.5 py-1.5 text-sm font-medium transition
                            {status === tab.key
                            ? 'bg-brand text-white'
                            : 'bg-muted text-muted-foreground hover:text-foreground'}"
                    >
                        {tab.label}
                        <span class="ml-1 tabular-nums opacity-70">{counts[tab.key]}</span>
                    </button>
                {/each}
            </div>
            <div class="relative min-w-56 flex-1">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    bind:value={search}
                    class="pl-9"
                    placeholder="Cari nama, nomor, atau telepon"
                    aria-label="Cari pendaftar"
                />
            </div>
        </div>

        <div class="overflow-x-auto px-5">
            <table class="w-full min-w-[48rem] text-sm">
                <thead>
                    <tr class="border-b text-left text-xs text-muted-foreground">
                        <th scope="col" class="py-2 font-medium">Nomor</th>
                        <th scope="col" class="py-2 font-medium">Nama</th>
                        <th scope="col" class="py-2 font-medium">Kelas</th>
                        <th scope="col" class="py-2 font-medium">Kontak orang tua</th>
                        <th scope="col" class="py-2 font-medium">Masuk</th>
                        <th scope="col" class="py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {#each rows as row (row.number)}
                        <tr class="border-b last:border-0">
                            <td class="py-3 pr-4 font-medium">
                                <Link
                                    href={`/admin/ppdb/${row.number}`}
                                    class="text-brand hover:underline"
                                >
                                    {row.number}
                                </Link>
                            </td>
                            <td class="py-3 pr-4">{row.full_name}</td>
                            <td class="py-3 pr-4 text-muted-foreground">{row.grade_target}</td>
                            <td class="py-3 pr-4 text-muted-foreground">{row.parent_phone}</td>
                            <td class="py-3 pr-4 text-muted-foreground">{row.created_at}</td>
                            <td class="py-3">
                                <Badge
                                    variant="outline"
                                    class={statusBadgeClass[row.status]}
                                >
                                    {statusLabels[row.status]}
                                </Badge>
                            </td>
                        </tr>
                    {:else}
                        <tr>
                            <td colspan="6" class="py-10 text-center text-muted-foreground">
                                Tidak ada pendaftar yang cocok. Ubah kata kunci atau filter.
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>

        <p class="px-5 text-xs text-muted-foreground">
            Menampilkan {rows.length} dari {registrations.length} pendaftar.
        </p>
    </Card>
</div>
