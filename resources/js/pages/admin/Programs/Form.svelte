<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Program', href: '/admin/programs' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import { untrack } from 'svelte';
    import ProgramController from '@/actions/App/Http/Controllers/Admin/ProgramController';
    import ImageUpload from '@/components/admin/ImageUpload.svelte';
    import RichEditor from '@/components/admin/RichEditor.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import Icon from '@/components/public/Icon.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { iconLibrary } from '@/lib/admin';

    type ProgramDetail = {
        slug: string;
        title: string;
        icon: string;
        badge: string | null;
        short_description: string | null;
        description: string | null;
        image: string | null;
        order: number;
        is_active: boolean;
    };

    let { program = null }: { program?: ProgramDetail | null } = $props();

    const editing = $derived(program !== null);
    const action = $derived(
        program === null
            ? ProgramController.store.form()
            : ProgramController.update.form({ program: program.slug }),
    );

    let icon = $state(untrack(() => program?.icon ?? 'book-open'));
    let shortDescription = $state(untrack(() => program?.short_description ?? ''));
    let description = $state(untrack(() => program?.description ?? ''));
    let isActive = $state(untrack(() => program?.is_active ?? true));
</script>

<AppHead title={editing ? 'Ubah program' : 'Tambah program'} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={editing ? 'Ubah program' : 'Tambah program'}
            description="Ringkasan tampil di kartu, penjelasan panjang di halaman detail."
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/programs" class={props.class}>
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
                    <Label for="title">Nama program</Label>
                    <Input
                        id="title"
                        name="title"
                        value={program?.title ?? ''}
                        required
                        placeholder="Mis. Tahfizh Al-Qur’an"
                    />
                    <InputError message={errors.title} />
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="badge">Label kartu</Label>
                    <Input
                        id="badge"
                        name="badge"
                        value={program?.badge ?? ''}
                        placeholder="Mis. Unggulan"
                    />
                    <InputError message={errors.badge} />
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="short_description">Ringkasan</Label>
                    <textarea
                        id="short_description"
                        name="short_description"
                        bind:value={shortDescription}
                        rows="2"
                        maxlength="160"
                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                        placeholder="Satu kalimat yang tampil di kartu program"
                    ></textarea>
                    <InputError message={errors.short_description} />
                    <p class="text-xs text-muted-foreground">
                        {shortDescription.length}/160 karakter
                    </p>
                </div>

                <div class="grid gap-2 px-5">
                    <Label for="description">Penjelasan lengkap</Label>
                    <RichEditor id="description" bind:value={description} />
                    <input type="hidden" name="description" value={description} />
                    <InputError message={errors.description} />
                </div>
            </Card>

            <div class="flex flex-col gap-4">
                <Card class="gap-5 py-5">
                    <div class="grid gap-2 px-5">
                        <Label for="icon">Ikon</Label>
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-muted">
                                <Icon name={icon} class="size-5" />
                            </span>
                            <select
                                id="icon"
                                name="icon"
                                bind:value={icon}
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                            >
                                {#each iconLibrary as option (option)}
                                    <option value={option}>{option}</option>
                                {/each}
                            </select>
                        </div>
                        <InputError message={errors.icon} />
                    </div>

                    <div class="grid gap-2 px-5">
                        <Label for="order">Urutan tampil</Label>
                        <Input
                            id="order"
                            name="order"
                            type="number"
                            min="1"
                            value={program?.order ?? 1}
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
                        <Label for="is_active">Tampilkan di situs publik</Label>
                    </div>
                </Card>

                <Card class="gap-4 py-5">
                    <div class="px-5">
                        <ImageUpload
                            id="image"
                            label="Gambar program"
                            current={program?.image ?? ''}
                        />
                        <InputError class="mt-2" message={errors.image} />
                    </div>
                </Card>

                <Button type="submit" class="w-full" disabled={processing}>
                    {processing
                        ? 'Menyimpan…'
                        : editing
                          ? 'Simpan perubahan'
                          : 'Simpan program'}
                </Button>
            </div>
        {/snippet}
    </Form>
</div>
