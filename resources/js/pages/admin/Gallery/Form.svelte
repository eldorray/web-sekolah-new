<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Galeri', href: '/admin/gallery' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import { untrack } from 'svelte';
    import GalleryController from '@/actions/App/Http/Controllers/Admin/GalleryController';
    import ImageUpload from '@/components/admin/ImageUpload.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type AlbumDetail = {
        slug: string;
        title: string;
        description: string | null;
        order: number;
        is_published: boolean;
        cover_image: string | null;
    };

    let { album = null }: { album?: AlbumDetail | null } = $props();

    const editing = $derived(album !== null);
    const action = $derived(
        album === null
            ? GalleryController.store.form()
            : GalleryController.update.form({ album: album.slug }),
    );

    let isPublished = $state(untrack(() => album?.is_published ?? true));
</script>

<AppHead title={editing ? 'Ubah album' : 'Tambah album'} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={editing ? 'Ubah album' : 'Tambah album'}
            description="Setelah album tersimpan, unggah fotonya lewat menu kelola foto."
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/gallery" class={props.class}>
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
                    <Label for="title">Judul album</Label>
                    <Input id="title" name="title" value={album?.title ?? ''} required placeholder="Mis. Kegiatan Sekolah 2026" />
                    <InputError message={errors.title} />
                </div>
                <div class="grid gap-2 px-5">
                    <Label for="description">Deskripsi</Label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        value={album?.description ?? ''}
                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                        placeholder="Keterangan singkat isi album"
                    ></textarea>
                    <InputError message={errors.description} />
                </div>
            </Card>

            <div class="flex flex-col gap-4">
                <Card class="gap-5 py-5">
                    <div class="grid gap-2 px-5">
                        <Label for="order">Urutan tampil</Label>
                        <Input id="order" name="order" type="number" min="1" value={album?.order ?? 1} required />
                        <InputError message={errors.order} />
                    </div>
                    <div class="flex items-center gap-3 px-5">
                        <input type="hidden" name="is_published" value={isPublished ? '1' : '0'} />
                        <Checkbox id="is_published" bind:checked={isPublished} />
                        <Label for="is_published">Terbitkan album</Label>
                    </div>
                </Card>

                <Card class="gap-4 py-5">
                    <div class="px-5">
                        <ImageUpload
                            id="cover_image"
                            label="Sampul album"
                            current={album?.cover_image ?? ''}
                        />
                        <InputError class="mt-2" message={errors.cover_image} />
                    </div>
                </Card>

                <Button type="submit" class="w-full" disabled={processing}>
                    {processing
                        ? 'Menyimpan…'
                        : editing
                          ? 'Simpan perubahan'
                          : 'Simpan album'}
                </Button>
            </div>
        {/snippet}
    </Form>
</div>
