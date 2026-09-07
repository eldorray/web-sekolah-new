<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Berita', href: '/admin/news' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import NewsController from '@/actions/App/Http/Controllers/Admin/NewsController';
    import ImageUpload from '@/components/admin/ImageUpload.svelte';
    import RichEditor from '@/components/admin/RichEditor.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type NewsDetail = {
        slug: string;
        title: string;
        category: string;
        excerpt: string | null;
        content: string | null;
        image: string | null;
        published_at: string | null;
        is_published: boolean;
    };

    let {
        news = null,
        categories = [],
    }: {
        news?: NewsDetail | null;
        categories?: string[];
    } = $props();

    const editing = $derived(news !== null);
    const action = $derived(
        news === null
            ? NewsController.store.form()
            : NewsController.update.form({ news: news.slug }),
    );

    // Initial values only; the page remounts when the record changes.
    let title = $state(untrack(() => news?.title ?? ''));
    let excerpt = $state(untrack(() => news?.excerpt ?? ''));
    let content = $state(untrack(() => news?.content ?? ''));
    let isPublished = $state(untrack(() => news?.is_published ?? true));
</script>

<AppHead title={editing ? 'Ubah berita' : 'Tulis berita'} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={editing ? 'Ubah berita' : 'Tulis berita'}
            description={editing
                ? 'Perubahan tercatat di audit log bersama nama editor.'
                : 'Isi konten, atur jadwal terbit, lalu simpan.'}
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/news" class={props.class}>
                    <ArrowLeft class="size-4" />
                    Kembali
                </Link>
            {/snippet}
        </Button>
    </div>

    <Form {...action} class="grid gap-6 lg:grid-cols-3">
        {#snippet children({ errors, processing })}
            <Card class="gap-5 py-5 lg:col-span-2">
                <div class="grid gap-2 px-5">
                    <Label for="title">Judul</Label>
                    <Input id="title" name="title" bind:value={title} required placeholder="Judul berita" />
                    <InputError message={errors.title} />
                    <p class="text-xs text-muted-foreground">
                        Slug dibuat dari judul dan dijaga unik oleh server.
                    </p>
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="excerpt">Kutipan</Label>
                    <textarea
                        id="excerpt"
                        name="excerpt"
                        bind:value={excerpt}
                        rows="2"
                        maxlength="200"
                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                        placeholder="Satu-dua kalimat yang tampil di daftar berita"
                    ></textarea>
                    <InputError message={errors.excerpt} />
                    <p class="text-xs text-muted-foreground">{excerpt.length}/200 karakter</p>
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="content">Isi berita</Label>
                    <RichEditor id="content" bind:value={content} />
                    <input type="hidden" name="content" value={content} />
                    <InputError message={errors.content} />
                    <p class="text-xs text-muted-foreground">
                        HTML dibersihkan di server sebelum disimpan.
                    </p>
                </div>
            </Card>

            <div class="flex flex-col gap-4">
                <Card class="gap-5 py-5">
                    <div class="grid gap-2 px-5">
                        <Label for="category">Kategori</Label>
                        <select
                            id="category"
                            name="category"
                            value={news?.category ?? categories[0]}
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                        >
                            {#each categories as option (option)}
                                <option value={option}>{option}</option>
                            {/each}
                        </select>
                        <InputError message={errors.category} />
                    </div>

                    <div class="grid gap-2 px-5">
                        <Label for="published_at">Tanggal terbit</Label>
                        <Input
                            id="published_at"
                            name="published_at"
                            type="date"
                            value={news?.published_at ?? new Date().toISOString().slice(0, 10)}
                            required
                        />
                        <InputError message={errors.published_at} />
                    </div>

                    <div class="flex items-center gap-3 px-5">
                        <!-- The shared Checkbox is a button, so the value is carried here. -->
                        <input
                            type="hidden"
                            name="is_published"
                            value={isPublished ? '1' : '0'}
                        />
                        <Checkbox id="is_published" bind:checked={isPublished} />
                        <Label for="is_published">Terbitkan sekarang</Label>
                    </div>
                </Card>

                <Card class="gap-4 py-5">
                    <div class="px-5">
                        <ImageUpload
                            id="image"
                            label="Gambar utama"
                            current={news?.image ?? ''}
                        />
                        <InputError class="mt-2" message={errors.image} />
                    </div>
                </Card>

                <Button type="submit" class="w-full" disabled={processing}>
                    {processing
                        ? 'Menyimpan…'
                        : editing
                          ? 'Simpan perubahan'
                          : 'Simpan berita'}
                </Button>
            </div>
        {/snippet}
    </Form>
</div>
