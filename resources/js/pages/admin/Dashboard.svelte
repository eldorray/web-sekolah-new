<script module lang="ts">
    export const layout = {
        breadcrumbs: [{ title: 'Panel Admin', href: '/admin' }],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Card } from '@/components/ui/card';
    import { statusBadgeClass, statusLabels, type PpdbStatus } from '@/lib/admin';

    type Tile = { key: string; label: string; value: string; hint: string };
    type Point = { date: string; visits: number };
    type Country = { country: string; code: string; visits: number };
    type Registration = {
        number: string;
        full_name: string;
        grade_target: string;
        created_at: string | null;
        status: PpdbStatus;
    };
    type Message = {
        id: number;
        name: string;
        email: string;
        subject: string;
        created_at: string | null;
    };

    let {
        summary = [],
        visitorSeries = [],
        topCountries = [],
        registrations = [],
        messages = [],
    }: {
        summary?: Tile[];
        visitorSeries?: Point[];
        topCountries?: Country[];
        registrations?: Registration[];
        messages?: Message[];
    } = $props();

    const peak = $derived(
        Math.max(1, ...visitorSeries.map((day) => day.visits)),
    );
    const peakDay = $derived(visitorSeries.find((day) => day.visits === peak));
    const countryPeak = $derived(
        Math.max(1, ...topCountries.map((row) => row.visits)),
    );

    let hovered = $state<number | null>(null);
</script>

<AppHead title="Panel Admin" />

<div class="flex flex-col gap-6 p-4">
    <Heading
        title="Panel Admin"
        description="Ringkasan kunjungan, pendaftar PPDB, dan pesan masuk."
    />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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

    <div class="grid gap-4 lg:grid-cols-3">
        <Card class="gap-4 py-5 lg:col-span-2">
            <div class="px-5">
                <h3 class="text-base font-medium">Kunjungan harian, 14 hari terakhir</h3>
                <p class="text-sm text-muted-foreground">
                    Puncak {peak} kunjungan pada {peakDay?.date}.
                </p>
            </div>

            <div class="relative px-5">
                {#if hovered !== null}
                    <div
                        class="pointer-events-none absolute -top-1 left-5 rounded-lg bg-foreground px-2.5 py-1.5 text-xs text-background shadow-md"
                        style="left: calc(1.25rem + {((hovered + 0.5) / visitorSeries.length) * 100}% - 3rem)"
                    >
                        {visitorSeries[hovered]?.date} · {visitorSeries[hovered]?.visits}
                    </div>
                {/if}

                <!-- Chart is decorative for assistive tech; the table below carries the data. -->
                <div class="flex h-44 items-end gap-0.5" aria-hidden="true">
                    {#each visitorSeries as day, index (day.date)}
                        <div
                            class="group flex h-full flex-1 cursor-default flex-col justify-end"
                            role="presentation"
                            onmouseenter={() => (hovered = index)}
                            onmouseleave={() => (hovered = null)}
                        >
                            {#if day.visits === peak}
                                <span class="mb-1 text-center text-[11px] font-semibold text-muted-foreground">
                                    {day.visits}
                                </span>
                            {/if}
                            <div
                                class="w-full rounded-t bg-brand-mark transition-opacity {hovered !==
                                    null && hovered !== index
                                    ? 'opacity-45'
                                    : ''}"
                                style="height: {(day.visits / peak) * 100}%"
                            ></div>
                        </div>
                    {/each}
                </div>
                <div class="mt-2 flex justify-between text-[11px] text-muted-foreground">
                    <span>{visitorSeries[0]?.date ?? ''}</span>
                    <span>{visitorSeries[visitorSeries.length - 1]?.date ?? ''}</span>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer text-xs text-muted-foreground hover:text-foreground">
                        Lihat data tabel
                    </summary>
                    <table class="mt-3 w-full text-sm">
                        <caption class="sr-only">
                            Kunjungan harian 14 hari terakhir
                        </caption>
                        <thead>
                            <tr class="border-b text-left text-xs text-muted-foreground">
                                <th scope="col" class="py-1.5 font-medium">Tanggal</th>
                                <th scope="col" class="py-1.5 text-right font-medium">Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each visitorSeries as day (day.date)}
                                <tr class="border-b last:border-0">
                                    <td class="py-1.5">{day.date}</td>
                                    <td class="py-1.5 text-right tabular-nums">{day.visits}</td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </details>
            </div>
        </Card>

        <Card class="gap-4 py-5">
            <div class="px-5">
                <h3 class="text-base font-medium">Negara pengunjung</h3>
                <p class="text-sm text-muted-foreground">30 hari terakhir.</p>
            </div>
            <ul class="space-y-3 px-5">
                {#each topCountries as row (row.code)}
                    <li>
                        <div class="flex items-baseline justify-between text-sm">
                            <span>{row.country}</span>
                            <span class="tabular-nums text-muted-foreground">{row.visits}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 w-full rounded-full bg-muted">
                            <div
                                class="h-1.5 rounded-full bg-brand-mark"
                                style="width: {(row.visits / countryPeak) * 100}%"
                            ></div>
                        </div>
                    </li>
                {/each}
            </ul>
        </Card>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <Card class="gap-4 py-5 lg:col-span-2">
            <div class="flex items-center justify-between px-5">
                <div>
                    <h3 class="text-base font-medium">Pendaftar PPDB terbaru</h3>
                    <p class="text-sm text-muted-foreground">
                        Enam pendaftar terakhir yang masuk.
                    </p>
                </div>
                <Link
                    href="/admin/ppdb"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-brand hover:underline"
                >
                    Semua pendaftar
                    <ArrowRight class="size-4" />
                </Link>
            </div>
            <div class="overflow-x-auto px-5">
                <table class="w-full min-w-[36rem] text-sm">
                    <thead>
                        <tr class="border-b text-left text-xs text-muted-foreground">
                            <th scope="col" class="py-2 font-medium">Nomor</th>
                            <th scope="col" class="py-2 font-medium">Nama</th>
                            <th scope="col" class="py-2 font-medium">Kelas</th>
                            <th scope="col" class="py-2 font-medium">Masuk</th>
                            <th scope="col" class="py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each registrations as row (row.number)}
                            <tr class="border-b last:border-0">
                                <td class="py-2.5">
                                    <Link
                                        href={`/admin/ppdb/${row.number}`}
                                        class="font-medium text-brand hover:underline"
                                    >
                                        {row.number}
                                    </Link>
                                </td>
                                <td class="py-2.5">{row.full_name}</td>
                                <td class="py-2.5 text-muted-foreground">{row.grade_target}</td>
                                <td class="py-2.5 text-muted-foreground">{row.created_at}</td>
                                <td class="py-2.5">
                                    <Badge
                                        variant="outline"
                                        class={statusBadgeClass[row.status]}
                                    >
                                        {statusLabels[row.status]}
                                    </Badge>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </Card>

        <Card class="gap-4 py-5">
            <div class="px-5">
                <h3 class="text-base font-medium">Pesan belum dibaca</h3>
                <p class="text-sm text-muted-foreground">{messages.length} pesan menunggu.</p>
            </div>
            <ul class="divide-y px-5">
                {#each messages as message (message.id)}
                    <li class="py-3 first:pt-0 last:pb-0">
                        <p class="text-sm font-medium">{message.subject}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {message.name} · {message.email}
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">{message.created_at}</p>
                    </li>
                {/each}
            </ul>
        </Card>
    </div>
</div>
