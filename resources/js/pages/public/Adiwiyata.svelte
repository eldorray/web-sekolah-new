<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import CircleCheckBig from '@lucide/svelte/icons/circle-check-big';
    import CircleDashed from '@lucide/svelte/icons/circle-dashed';
    import CircleDot from '@lucide/svelte/icons/circle-dot';
    import Lock from '@lucide/svelte/icons/lock';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import AdiwiyataController from '@/actions/App/Http/Controllers/Public/AdiwiyataController';
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import PageHero from '@/components/public/PageHero.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';

    type Status = 'ok' | 'partial' | 'empty';
    type Folder = { key: string; title: string };
    type Group = { key: string; title: string; children: Folder[] };
    type Assessment = { status: Status; note: string | null };

    let {
        tree = [],
        assessments = {},
        canEdit = false,
    }: {
        tree?: Group[];
        assessments?: Record<string, Assessment>;
        canEdit?: boolean;
    } = $props();

    const statusLabels: Record<Status, string> = {
        ok: 'Lengkap',
        partial: 'Sebagian',
        empty: 'Kosong',
    };

    const statusIcon = {
        ok: CircleCheckBig,
        partial: CircleDot,
        empty: CircleDashed,
    } as const;

    const statusClass: Record<Status, string> = {
        ok: 'bg-emerald-100 text-emerald-800',
        partial: 'bg-amber-100 text-amber-900',
        empty: 'bg-slate-100 text-slate-600',
    };

    const folders = $derived(tree.flatMap((group) => group.children));
    const done = $derived(
        folders.filter((folder) => assessments[folder.key]?.status === 'ok').length,
    );
    const progress = $derived(
        folders.length === 0 ? 0 : Math.round((done / folders.length) * 100),
    );

    function statusOf(key: string): Status {
        return assessments[key]?.status ?? 'empty';
    }

    function save(folder: Folder, status: string, note: string): void {
        router.post(
            AdiwiyataController.save.url(),
            { folder_key: folder.key, status, note },
            { preserveScroll: true },
        );
    }
</script>

<AppHead title="Monitor Adiwiyata" />
<PageHero
    title="Monitor Adiwiyata"
    crumb="Adiwiyata"
    image="https://picsum.photos/seed/dh-adiwiyata/1600/500"
/>

<section class="bg-white py-16">
    <div class="shell max-w-4xl">
        <SectionHead
            eyebrow="Progres dokumen"
            title="Kelengkapan berkas"
            accent="{progress}%"
            text="{done} dari {folders.length} folder sudah lengkap. Progres terbuka untuk publik, penilaian hanya bisa diubah pemegang PIN."
        />

        <div use:reveal class="mt-8 h-3 w-full overflow-hidden rounded-full bg-mist">
            <div class="h-3 rounded-full bg-brand" style="width: {progress}%"></div>
        </div>

        {#if canEdit}
            <div
                class="mt-10 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-brand-soft px-5 py-3"
            >
                <p class="flex items-center gap-2 text-sm font-semibold text-brand-deep">
                    <ShieldCheck class="size-4" />
                    Mode penilaian aktif — Anda masuk sebagai admin.
                </p>
                <button
                    type="button"
                    class="text-sm font-semibold text-brand-deep underline"
                    onclick={() =>
                        router.post(
                            AdiwiyataController.reset.url(),
                            {},
                            { preserveScroll: true },
                        )}
                >
                    Reset semua penilaian
                </button>
            </div>
        {:else}
            <p
                class="mt-10 flex flex-wrap items-center justify-center gap-2 rounded-xl bg-mist px-5 py-4 text-center text-sm text-ink-soft"
            >
                <Lock class="size-4" />
                Daftar folder terbuka untuk publik. Untuk mengubah status,
                <Link href="/login" class="font-semibold text-brand underline">
                    masuk sebagai admin
                </Link>.
            </p>
        {/if}

        <div class="mt-10 space-y-8">
            {#each tree as group (group.key)}
                <section>
                    <h3 class="font-display text-lg font-bold text-ink">{group.title}</h3>
                    <ul class="mt-4 space-y-3">
                        {#each group.children as folder, folderIndex (folder.key)}
                            {@const status = statusOf(folder.key)}
                            {@const StatusIcon = statusIcon[status]}
                            <li
                                use:reveal={{ delay: folderIndex * 60, y: 12 }}
                                class="rounded-xl border border-brand-soft p-4"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="flex items-center gap-2 text-sm font-medium text-ink">
                                        <StatusIcon class="size-4 text-brand" />
                                        {folder.title}
                                    </p>
                                    <span
                                        class="rounded-full px-3 py-1 font-display text-xs font-bold {statusClass[
                                            status
                                        ]}"
                                    >
                                        {statusLabels[status]}
                                    </span>
                                </div>

                                {#if canEdit}
                                    <form
                                        class="mt-3 grid gap-2 sm:grid-cols-[10rem_1fr_auto]"
                                        onsubmit={(event) => {
                                            event.preventDefault();
                                            const data = new FormData(
                                                event.currentTarget as HTMLFormElement,
                                            );
                                            save(
                                                folder,
                                                String(data.get('status')),
                                                String(data.get('note') ?? ''),
                                            );
                                        }}
                                    >
                                        <select
                                            name="status"
                                            class="field !py-2"
                                            aria-label="Status {folder.title}"
                                            value={status}
                                        >
                                            <option value="ok">Lengkap</option>
                                            <option value="partial">Sebagian</option>
                                            <option value="empty">Kosong</option>
                                        </select>
                                        <input
                                            name="note"
                                            class="field !py-2"
                                            placeholder="Catatan penilaian"
                                            aria-label="Catatan {folder.title}"
                                            value={assessments[folder.key]?.note ?? ''}
                                        />
                                        <button type="submit" class="btn-brand !py-2">
                                            Simpan
                                        </button>
                                    </form>
                                {:else if assessments[folder.key]?.note}
                                    <p class="mt-2 text-xs text-ink-soft">
                                        {assessments[folder.key]?.note}
                                    </p>
                                {/if}
                            </li>
                        {/each}
                    </ul>
                </section>
            {/each}
        </div>
    </div>
</section>
