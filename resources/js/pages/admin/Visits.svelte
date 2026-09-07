<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Kunjungan', href: '/admin/visits' },
        ],
    };
</script>

<script lang="ts">
    import DataTable from '@/components/admin/DataTable.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Card } from '@/components/ui/card';
    import { router } from '@inertiajs/svelte';
    import VisitController from '@/actions/App/Http/Controllers/Admin/VisitController';
    import {
        visitStatusClass,
        visitStatusLabels,
        type VisitStatus,
    } from '@/lib/admin';

    type Visit = {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        visit_date: string | null;
        participants: number;
        purpose: string;
        status: VisitStatus;
    };

    let { visits = [] }: { visits?: Visit[] } = $props();

    let filter = $state<'SEMUA' | VisitStatus>('SEMUA');

    const visible = $derived(
        filter === 'SEMUA' ? visits : visits.filter((row) => row.status === filter),
    );

    const tabs: Array<{ key: 'SEMUA' | VisitStatus; label: string }> = [
        { key: 'SEMUA', label: 'Semua' },
        { key: 'pending', label: 'Menunggu' },
        { key: 'approved', label: 'Disetujui' },
        { key: 'done', label: 'Selesai' },
        { key: 'rejected', label: 'Ditolak' },
    ];

    const columns = [
        { label: 'Pemohon' },
        { label: 'Tanggal' },
        { label: 'Peserta' },
        { label: 'Tujuan' },
        { label: 'Status' },
    ];
</script>

<AppHead title="Jadwal kunjungan" />

<div class="flex flex-col gap-6 p-4">
    <Heading
        title="Jadwal kunjungan"
        description="Permintaan kunjungan sekolah dari formulir publik."
    />

    <Card class="gap-4 py-5">
        <div class="flex flex-wrap gap-1 px-5" role="group" aria-label="Filter status">
            {#each tabs as tab (tab.key)}
                <button
                    type="button"
                    onclick={() => (filter = tab.key)}
                    aria-pressed={filter === tab.key}
                    class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {filter ===
                    tab.key
                        ? 'bg-brand text-white'
                        : 'bg-muted text-muted-foreground hover:text-foreground'}"
                >
                    {tab.label}
                </button>
            {/each}
        </div>

        <DataTable
            {columns}
            isEmpty={visible.length === 0}
            empty="Tidak ada permintaan kunjungan pada status ini."
            minWidth="48rem"
        >
            {#snippet rows()}
                {#each visible as row (row.id)}
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4">
                            <p class="font-medium">{row.name}</p>
                            <p class="text-xs text-muted-foreground">
                                {row.email} · {row.phone}
                            </p>
                        </td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">
                            {row.visit_date}
                        </td>
                        <td class="py-3 pr-4 tabular-nums text-muted-foreground">
                            {row.participants} orang
                        </td>
                        <td class="max-w-xs py-3 pr-4 text-muted-foreground">{row.purpose}</td>
                        <td class="py-3">
                            <select
                                aria-label={`Status kunjungan ${row.name}`}
                                value={row.status}
                                onchange={(event) =>
                                    router.put(
                                        VisitController.update.url({ visit: row.id }),
                                        {
                                            status: (
                                                event.currentTarget as HTMLSelectElement
                                            ).value,
                                        },
                                        { preserveScroll: true },
                                    )}
                                class="h-8 rounded-md border border-input bg-transparent px-2 text-xs shadow-xs"
                            >
                                {#each tabs.slice(1) as option (option.key)}
                                    <option value={option.key}>{option.label}</option>
                                {/each}
                            </select>
                            <Badge
                                variant="outline"
                                class="ml-2 {visitStatusClass[row.status]}"
                            >
                                {visitStatusLabels[row.status]}
                            </Badge>
                        </td>
                    </tr>
                {/each}
            {/snippet}
        </DataTable>

        <p class="px-5 text-xs text-muted-foreground">
            Menampilkan {visible.length} dari {visits.length} permintaan.
        </p>
    </Card>
</div>
