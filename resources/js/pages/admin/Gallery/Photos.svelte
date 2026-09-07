<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Galeri', href: '/admin/gallery' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link, router } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Upload from '@lucide/svelte/icons/upload';
    import GalleryController from '@/actions/App/Http/Controllers/Admin/GalleryController';
    import ConfirmDelete from '@/components/admin/ConfirmDelete.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';

    type Photo = {
        id: number;
        thumbnail: string;
        caption: string | null;
        order: number;
    };

    let {
        slug,
        album,
    }: {
        slug: string;
        album: { title: string; photos: Photo[] };
    } = $props();

    let pendingDelete = $state<number | null>(null);
    let queued = $state<string[]>([]);

    const target = $derived(
        album.photos.find((photo) => photo.id === pendingDelete) ?? null,
    );

    function saveCaption(photo: Photo, caption: string): void {
        if (caption === (photo.caption ?? '')) {
            return;
        }

        router.put(
            GalleryController.updatePhoto.url({ photo: photo.id }),
            { caption, order: photo.order },
            { preserveScroll: true },
        );
    }
</script>

<AppHead title={`Foto — ${album.title}`} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={album.title}
            description={`${album.photos.length} foto dalam album ini. Urutan mengikuti nomor pada kartu.`}
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/gallery" class={props.class}>
                    <ArrowLeft class="size-4" />
                    Kembali ke album
                </Link>
            {/snippet}
        </Button>
    </div>

    <Card class="gap-4 py-5">
        <Form
            {...GalleryController.storePhotos.form({ album: slug })}
            class="px-5"
            options={{ preserveScroll: true }}
        >
            {#snippet children({ errors, processing })}
                <label class="flex cursor-pointer flex-col items-center gap-2 rounded-lg border-2 border-dashed p-8 text-center hover:bg-muted/50">
                    <Upload class="size-6 text-muted-foreground" />
                    <span class="text-sm font-medium">Pilih foto</span>
                    <span class="text-xs text-muted-foreground">
                        Bisa pilih banyak sekaligus. JPG atau PNG, maksimal 2 MB per foto.
                    </span>
                    <input
                        type="file"
                        name="photos[]"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        class="sr-only"
                        onchange={(event) => {
                            const input = event.currentTarget as HTMLInputElement;
                            queued = Array.from(input.files ?? []).map((file) => file.name);
                        }}
                    />
                </label>

                <InputError class="mt-2" message={errors.photos} />

                {#if queued.length > 0}
                    <ul class="mt-3 space-y-1 text-xs text-muted-foreground">
                        {#each queued as name (name)}
                            <li>{name}</li>
                        {/each}
                    </ul>
                    <Button type="submit" class="mt-4" disabled={processing}>
                        {processing ? 'Mengunggah…' : `Unggah ${queued.length} foto`}
                    </Button>
                {/if}
            {/snippet}
        </Form>
    </Card>

    {#if album.photos.length > 0}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {#each album.photos as photo (photo.id)}
                <Card class="gap-3 overflow-hidden py-0">
                    <img
                        src={photo.thumbnail}
                        alt={photo.caption ?? ''}
                        loading="lazy"
                        class="h-40 w-full object-cover"
                    />
                    <div class="grid gap-2 px-4 pb-4">
                        <Input
                            value={photo.caption ?? ''}
                            aria-label="Keterangan foto"
                            placeholder="Keterangan foto"
                            onblur={(event: FocusEvent) =>
                                saveCaption(
                                    photo,
                                    (event.currentTarget as HTMLInputElement).value,
                                )}
                        />
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                Urutan {photo.order}
                            </span>
                            <Button
                                variant="ghost"
                                size="icon"
                                onclick={() => (pendingDelete = photo.id)}
                            >
                                <Trash2 class="size-4 text-destructive" />
                                <span class="sr-only">Hapus foto</span>
                            </Button>
                        </div>
                    </div>
                </Card>
            {/each}
        </div>
    {:else}
        <p class="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground">
            Album ini belum punya foto. Unggah lewat kotak di atas.
        </p>
    {/if}
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus foto ini?"
    description={`"${target?.caption ?? 'Foto tanpa keterangan'}" akan dihapus dari album.`}
    confirmLabel="Hapus foto"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(GalleryController.destroyPhoto.url({ photo: target.id }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
