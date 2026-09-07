<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Berita', href: '/admin/news' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import NewsController from '@/actions/App/Http/Controllers/Admin/NewsController';
    import ConfirmDelete from '@/components/admin/ConfirmDelete.svelte';

    type Row = {
        slug: string;
        title: string;
        category: string;
        author: string | null;
        published_at: string | null;
        is_published: boolean;
    };

    let {
        news = [],
        categories = [],
    }: {
        news?: Row[];
        categories?: string[];
    } = $props();

    // ponytail: filtering runs client-side over the full list. Move to a
    // server-side paginator when the list outgrows one page.
    let search = $state('');
    let category = $state('SEMUA');
    let status = $state('SEMUA');
    let pendingDelete = $state<string | null>(null);

    const rows = $derived(
        news.filter((row) => {
            const matchesSearch = row.title
                .toLowerCase()
                .includes(search.trim().toLowerCase());
            const matchesCategory =
                category === 'SEMUA' || row.category === category;
            const matchesStatus =
                status === 'SEMUA' ||
                (status === 'TERBIT' ? row.is_published : !row.is_published);

            return matchesSearch && matchesCategory && matchesStatus;
        }),
    );

    const target = $derived(news.find((row) => row.slug === pendingDelete) ?? null);

    function confirmDelete(): void {
        if (target === null) {
            return;
        }

        router.delete(NewsController.destroy.url({ news: target.slug }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }
</script>

<AppHead title="Berita" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Berita"
            description="Kelola artikel, pengumuman, dan liputan kegiatan."
        />
        <div class="flex gap-2">
            <Button asChild>
                {#snippet children(props)}
                    <Link {...props} href="/admin/news/create" class={props.class}>
                        <Plus class="size-4" />
                        Tulis berita
                    </Link>
                {/snippet}
            </Button>
        </div>
    </div>

    <Card class="gap-4 py-5">
        <div class="flex flex-wrap gap-3 px-5">
            <div class="relative min-w-56 flex-1">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    bind:value={search}
                    class="pl-9"
                    placeholder="Cari judul berita"
                    aria-label="Cari judul berita"
                />
            </div>
            <div>
                <Label for="filter-category" class="sr-only">Kategori</Label>
                <select
                    id="filter-category"
                    bind:value={category}
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                >
                    <option value="SEMUA">Semua kategori</option>
                    {#each categories as option (option)}
                        <option value={option}>{option}</option>
                    {/each}
                </select>
            </div>
            <div>
                <Label for="filter-status" class="sr-only">Status</Label>
                <select
                    id="filter-status"
                    bind:value={status}
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                >
                    <option value="SEMUA">Semua status</option>
                    <option value="TERBIT">Terbit</option>
                    <option value="DRAF">Draf</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto px-5">
            <table class="w-full min-w-[44rem] text-sm">
                <thead>
                    <tr class="border-b text-left text-xs text-muted-foreground">
                        <th scope="col" class="py-2 font-medium">Judul</th>
                        <th scope="col" class="py-2 font-medium">Kategori</th>
                        <th scope="col" class="py-2 font-medium">Penulis</th>
                        <th scope="col" class="py-2 font-medium">Terbit</th>
                        <th scope="col" class="py-2 font-medium">Status</th>
                        <th scope="col" class="py-2 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {#each rows as row (row.slug)}
                        <tr class="border-b last:border-0">
                            <td class="max-w-sm py-3 pr-4 font-medium">{row.title}</td>
                            <td class="py-3 pr-4 text-muted-foreground">{row.category}</td>
                            <td class="py-3 pr-4 text-muted-foreground">{row.author}</td>
                            <td class="py-3 pr-4 text-muted-foreground tabular-nums">
                                {row.published_at}
                            </td>
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
                                                href={`/admin/news/${row.slug}/edit`}
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
                    {:else}
                        <tr>
                            <td colspan="6" class="py-10 text-center text-muted-foreground">
                                Tidak ada berita yang cocok. Ubah kata kunci atau filter.
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>

        <p class="px-5 text-xs text-muted-foreground">
            Menampilkan {rows.length} dari {news.length} berita.
        </p>
    </Card>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus berita ini?"
    description={`"${target?.title}" akan dipindahkan ke tempat sampah. Berita bisa dipulihkan admin selama 30 hari.`}
    confirmLabel="Hapus berita"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={confirmDelete}
/>

