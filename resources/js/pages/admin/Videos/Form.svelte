<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Video', href: '/admin/videos' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import { untrack } from 'svelte';
    import VideoController from '@/actions/App/Http/Controllers/Admin/VideoController';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type VideoDetail = {
        id: number;
        title: string;
        youtube_id: string;
        description: string | null;
        order: number;
        is_active: boolean;
    };

    let { video = null }: { video?: VideoDetail | null } = $props();

    const editing = $derived(video !== null);
    const action = $derived(
        video === null
            ? VideoController.store.form()
            : VideoController.update.form({ video: video.id }),
    );

    let youtube = $state(untrack(() => video?.youtube_id ?? ''));
    let isActive = $state(untrack(() => video?.is_active ?? true));

    /** Same patterns the server accepts, so the preview matches what saves. */
    const previewId = $derived.by(() => {
        const value = youtube.trim();

        if (/^[A-Za-z0-9_-]{11}$/.test(value)) {
            return value;
        }

        const match = value.match(
            /(?:[?&]v=|youtu\.be\/|\/embed\/|\/shorts\/|\/live\/)([A-Za-z0-9_-]{11})/,
        );

        return match ? match[1] : '';
    });
</script>

<AppHead title={editing ? 'Ubah video' : 'Tambah video'} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={editing ? 'Ubah video' : 'Tambah video'}
            description="Tempel alamat video YouTube. Thumbnail diambil langsung dari YouTube."
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/videos" class={props.class}>
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
                    <Label for="title">Judul video</Label>
                    <Input
                        id="title"
                        name="title"
                        value={video?.title ?? ''}
                        required
                        placeholder="Mis. Profil Sekolah"
                    />
                    <InputError message={errors.title} />
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="youtube">Alamat YouTube</Label>
                    <Input
                        id="youtube"
                        name="youtube"
                        bind:value={youtube}
                        required
                        placeholder="https://www.youtube.com/watch?v=..."
                    />
                    <InputError message={errors.youtube} />
                    <p class="text-xs text-muted-foreground">
                        Menerima tautan biasa, youtu.be, /shorts/, /live/, atau ID
                        videonya saja.
                    </p>
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="description">Keterangan singkat</Label>
                    <Input
                        id="description"
                        name="description"
                        value={video?.description ?? ''}
                        maxlength={200}
                        placeholder="Satu kalimat di bawah judul"
                    />
                    <InputError message={errors.description} />
                </div>
            </Card>

            <div class="flex flex-col gap-4">
                <Card class="gap-5 py-5">
                    <div class="grid gap-2 px-5">
                        <Label for="order">Urutan tampil</Label>
                        <Input
                            id="order"
                            name="order"
                            type="number"
                            min="1"
                            value={video?.order ?? 1}
                            required
                        />
                        <InputError message={errors.order} />
                    </div>
                    <div class="flex items-center gap-3 px-5">
                        <input
                            type="hidden"
                            name="is_active"
                            value={isActive ? '1' : '0'}
                        />
                        <Checkbox id="is_active" bind:checked={isActive} />
                        <Label for="is_active">Tampilkan di beranda</Label>
                    </div>
                </Card>

                <Card class="gap-3 py-5">
                    <div class="px-5">
                        <p class="text-sm font-medium">Pratinjau</p>
                        {#if previewId}
                            <img
                                src={`https://i.ytimg.com/vi/${previewId}/hqdefault.jpg`}
                                alt="Thumbnail video"
                                class="mt-2 aspect-video w-full rounded-lg object-cover"
                            />
                            <p class="mt-2 font-mono text-xs text-muted-foreground">
                                {previewId}
                            </p>
                        {:else}
                            <p class="mt-2 text-sm text-muted-foreground">
                                Tempel alamat YouTube untuk melihat thumbnail-nya.
                            </p>
                        {/if}
                    </div>
                </Card>

                <Button type="submit" class="w-full" disabled={processing}>
                    {processing
                        ? 'Menyimpan…'
                        : editing
                          ? 'Simpan perubahan'
                          : 'Simpan video'}
                </Button>
            </div>
        {/snippet}
    </Form>
</div>
