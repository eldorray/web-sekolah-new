<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Brosur', href: '/admin/brochures' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import FileUp from '@lucide/svelte/icons/file-up';
    import { untrack } from 'svelte';
    import BrochureController from '@/actions/App/Http/Controllers/Admin/BrochureController';
    import ImageUpload from '@/components/admin/ImageUpload.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type BrochureDetail = {
        id: number;
        title: string;
        subtitle: string | null;
        order: number;
        is_active: boolean;
        preview_image: string | null;
        file_url: string | null;
    };

    let { brochure = null }: { brochure?: BrochureDetail | null } = $props();

    const editing = $derived(brochure !== null);
    const action = $derived(
        brochure === null
            ? BrochureController.store.form()
            : BrochureController.update.form({ brochure: brochure.id }),
    );

    let isActive = $state(untrack(() => brochure?.is_active ?? true));
    let pdfName = $state('');
</script>

<AppHead title={editing ? 'Ubah brosur' : 'Tambah brosur'} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={editing ? 'Ubah brosur' : 'Tambah brosur'}
            description="Gambar sampul tampil di situs, PDF-nya bisa diunduh pengunjung."
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/brochures" class={props.class}>
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
                    <Label for="title">Judul brosur</Label>
                    <Input id="title" name="title" value={brochure?.title ?? ''} required placeholder="Mis. Brosur PPDB 2027/2028" />
                    <InputError message={errors.title} />
                </div>
                <div class="grid gap-2 px-5">
                    <Label for="subtitle">Subjudul</Label>
                    <Input id="subtitle" name="subtitle" value={brochure?.subtitle ?? ''} placeholder="Mis. Gelombang I & II" />
                    <InputError message={errors.subtitle} />
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="file">Berkas PDF</Label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border-2 border-dashed p-5 hover:bg-muted/50">
                        <FileUp class="size-5 text-muted-foreground" />
                        <span>
                            <span class="block text-sm font-medium">
                                {pdfName || (brochure?.file_url ? 'Ganti berkas PDF' : 'Pilih berkas PDF')}
                            </span>
                            <span class="block text-xs text-muted-foreground">
                                PDF, maksimal 8 MB. Opsional.
                            </span>
                        </span>
                        <input
                            id="file"
                            name="file"
                            type="file"
                            accept="application/pdf"
                            class="sr-only"
                            onchange={(event) => {
                                const input = event.currentTarget as HTMLInputElement;
                                pdfName = input.files?.[0]?.name ?? '';
                            }}
                        />
                    </label>
                    <InputError message={errors.file} />
                    {#if brochure?.file_url}
                        <a
                            href={brochure.file_url}
                            target="_blank"
                            rel="noreferrer"
                            class="text-xs text-brand hover:underline"
                        >
                            Lihat berkas saat ini
                        </a>
                    {/if}
                </div>
            </Card>

            <div class="flex flex-col gap-4">
                <Card class="gap-5 py-5">
                    <div class="grid gap-2 px-5">
                        <Label for="order">Urutan tampil</Label>
                        <Input id="order" name="order" type="number" min="1" value={brochure?.order ?? 1} required />
                        <InputError message={errors.order} />
                    </div>
                    <div class="flex items-center gap-3 px-5">
                        <input type="hidden" name="is_active" value={isActive ? '1' : '0'} />
                        <Checkbox id="is_active" bind:checked={isActive} />
                        <Label for="is_active">Tampilkan di situs publik</Label>
                    </div>
                </Card>

                <Card class="gap-4 py-5">
                    <div class="px-5">
                        <ImageUpload
                            id="preview_image"
                            label="Gambar sampul"
                            current={brochure?.preview_image ?? ''}
                        />
                        <InputError class="mt-2" message={errors.preview_image} />
                    </div>
                </Card>

                <Button type="submit" class="w-full" disabled={processing}>
                    {processing
                        ? 'Menyimpan…'
                        : editing
                          ? 'Simpan perubahan'
                          : 'Simpan brosur'}
                </Button>
            </div>
        {/snippet}
    </Form>
</div>
