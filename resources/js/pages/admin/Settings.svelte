<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Pengaturan', href: '/admin/settings' },
        ],
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import { untrack } from 'svelte';
    import SettingController from '@/actions/App/Http/Controllers/Admin/SettingController';
    import ImageUpload from '@/components/admin/ImageUpload.svelte';
    import InputError from '@/components/InputError.svelte';
    import Icon from '@/components/public/Icon.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    type Highlight = { icon: string; title: string; text: string };
    type Slide = {
        eyebrow: string;
        title: string;
        accent: string | null;
        title_end: string | null;
        text: string;
        image?: string | null;
        image_url?: string | null;
    };
    type Step = { title: string; text: string };

    let {
        values = {},
        images = {},
        highlights = [],
        slides = [],
        aboutPoints = [],
        ppdbSteps = [],
        icons = [],
    }: {
        values?: Record<string, string | null>;
        images?: Record<string, string | null>;
        highlights?: Highlight[];
        slides?: Slide[];
        aboutPoints?: Highlight[];
        ppdbSteps?: Step[];
        icons?: string[];
    } = $props();

    // Local copy so rows can be added and removed before saving.
    let cards = $state<Highlight[]>(
        untrack(() => highlights.map((card) => ({ ...card }))),
    );

    let deck = $state<Slide[]>(untrack(() => slides.map((slide) => ({ ...slide }))));
    let points = $state<Highlight[]>(
        untrack(() => aboutPoints.map((point) => ({ ...point }))),
    );
    let steps = $state<Step[]>(untrack(() => ppdbSteps.map((step) => ({ ...step }))));

    const blankHighlight = (): Highlight => ({
        icon: icons[0] ?? 'book-open',
        title: '',
        text: '',
    });

    function addCard(): void {
        cards = [...cards, blankHighlight()];
    }

    function removeCard(index: number): void {
        cards = cards.filter((_, position) => position !== index);
    }

    function addSlide(): void {
        deck = [
            ...deck,
            {
                eyebrow: '',
                title: '',
                accent: '',
                title_end: '',
                text: '',
                image: null,
                image_url: null,
            },
        ];
    }

    function removeSlide(index: number): void {
        deck = deck.filter((_, position) => position !== index);
    }

    function addPoint(): void {
        points = [...points, blankHighlight()];
    }

    function removePoint(index: number): void {
        points = points.filter((_, position) => position !== index);
    }

    function addStep(): void {
        steps = [...steps, { title: '', text: '' }];
    }

    function removeStep(index: number): void {
        steps = steps.filter((_, position) => position !== index);
    }

    const tabs = [
        { key: 'identity', label: 'Identitas' },
        { key: 'profile', label: 'Profil' },
        { key: 'home', label: 'Beranda' },
        { key: 'ppdb', label: 'PPDB' },
        { key: 'branding', label: 'Branding' },
    ] as const;

    let tab = $state<(typeof tabs)[number]['key']>('identity');

    const identityFields: Array<{
        key: string;
        label: string;
        required?: boolean;
    }> = [
        { key: 'school_name', label: 'Nama sekolah', required: true },
        { key: 'foundation_name', label: 'Nama yayasan' },
        { key: 'headmaster_name', label: 'Kepala sekolah' },
        { key: 'npsn', label: 'NPSN' },
        { key: 'phone', label: 'Telepon' },
        { key: 'whatsapp', label: 'WhatsApp' },
        { key: 'email', label: 'Email' },
        { key: 'hours', label: 'Jam layanan' },
        { key: 'instagram', label: 'Instagram' },
        { key: 'facebook', label: 'Facebook' },
        { key: 'youtube', label: 'YouTube' },
    ];
</script>

<AppHead title="Pengaturan" />

<div class="flex flex-col gap-6 p-4">
    <Heading
        title="Pengaturan"
        description="Identitas sekolah, branding, dan kredensial alat internal."
    />

    <div class="flex flex-wrap gap-1" role="tablist" aria-label="Bagian pengaturan">
        {#each tabs as item (item.key)}
            <button
                type="button"
                role="tab"
                aria-selected={tab === item.key}
                onclick={() => (tab = item.key)}
                class="rounded-full px-4 py-1.5 text-sm font-medium transition {tab ===
                item.key
                    ? 'bg-brand text-white'
                    : 'bg-muted text-muted-foreground hover:text-foreground'}"
            >
                {item.label}
            </button>
        {/each}
    </div>

    {#if tab === 'identity'}
        <Form {...SettingController.updateIdentity.form()} class="max-w-3xl">
            {#snippet children({ errors, processing })}
                <Card class="gap-5 py-5">
                    <div class="grid gap-5 px-5 sm:grid-cols-2">
                        {#each identityFields as field (field.key)}
                            <div class="grid gap-2">
                                <Label for={field.key}>{field.label}</Label>
                                <Input
                                    id={field.key}
                                    name={field.key}
                                    value={values[field.key] ?? ''}
                                    required={field.required ?? false}
                                />
                                <InputError message={errors[field.key]} />
                            </div>
                        {/each}

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="address">Alamat</Label>
                            <Input id="address" name="address" value={values.address ?? ''} />
                            <InputError message={errors.address} />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="map_embed">URL peta (embed)</Label>
                            <Input id="map_embed" name="map_embed" value={values.map_embed ?? ''} />
                            <InputError message={errors.map_embed} />
                        </div>
                    </div>
                    <div class="px-5">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Simpan identitas'}
                        </Button>
                    </div>
                </Card>
            {/snippet}
        </Form>
    {:else if tab === 'profile'}
        <Form {...SettingController.updateProfile.form()} class="max-w-3xl">
            {#snippet children({ errors, processing })}
                <Card class="gap-5 py-5">
                    <div class="grid gap-5 px-5 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="accreditation">Akreditasi</Label>
                            <Input
                                id="accreditation"
                                name="accreditation"
                                value={values.accreditation ?? ''}
                                placeholder="Mis. A (2024)"
                            />
                            <InputError message={errors.accreditation} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="curriculum">Kurikulum</Label>
                            <Input
                                id="curriculum"
                                name="curriculum"
                                value={values.curriculum ?? ''}
                                placeholder="Mis. Merdeka"
                            />
                            <InputError message={errors.curriculum} />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="about_body">Profil singkat</Label>
                            <textarea
                                id="about_body"
                                name="about_body"
                                rows="4"
                                value={values.about_body ?? ''}
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                placeholder="Paragraf yang tampil di beranda dan halaman Tentang Kami"
                            ></textarea>
                            <InputError message={errors.about_body} />
                        </div>

                        <div class="grid gap-2">
                            <Label for="about_heading">Judul seksi profil</Label>
                            <Input
                                id="about_heading"
                                name="about_heading"
                                value={values.about_heading ?? ''}
                                placeholder="Mis. Berdiri 2005, tumbuh bersama"
                            />
                            <InputError message={errors.about_heading} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="about_heading_accent">
                                Kata beraksen (oranye)
                            </Label>
                            <Input
                                id="about_heading_accent"
                                name="about_heading_accent"
                                value={values.about_heading_accent ?? ''}
                                placeholder="warga sekitar"
                            />
                            <InputError message={errors.about_heading_accent} />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="vision">Visi</Label>
                            <textarea
                                id="vision"
                                name="vision"
                                rows="3"
                                value={values.vision ?? ''}
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                            ></textarea>
                            <InputError message={errors.vision} />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="vision_note">Keterangan di bawah visi</Label>
                            <textarea
                                id="vision_note"
                                name="vision_note"
                                rows="2"
                                maxlength="300"
                                value={values.vision_note ?? ''}
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                placeholder="Kosongkan bila tak perlu"
                            ></textarea>
                            <InputError message={errors.vision_note} />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="mission">Misi</Label>
                            <textarea
                                id="mission"
                                name="mission"
                                rows="6"
                                value={values.mission ?? ''}
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                placeholder="Satu poin misi per baris"
                            ></textarea>
                            <InputError message={errors.mission} />
                            <p class="text-xs text-muted-foreground">
                                Satu baris = satu poin misi di halaman Tentang Kami.
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="stat_students">Jumlah siswa</Label>
                            <Input
                                id="stat_students"
                                name="stat_students"
                                value={values.stat_students ?? ''}
                            />
                            <InputError message={errors.stat_students} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="stat_trophies">Jumlah piala</Label>
                            <Input
                                id="stat_trophies"
                                name="stat_trophies"
                                value={values.stat_trophies ?? ''}
                            />
                            <InputError message={errors.stat_trophies} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="stat_years">Tahun melayani</Label>
                            <Input
                                id="stat_years"
                                name="stat_years"
                                value={values.stat_years ?? ''}
                            />
                            <InputError message={errors.stat_years} />
                        </div>
                    </div>
                    <div class="px-5">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Simpan profil'}
                        </Button>
                    </div>
                </Card>
            {/snippet}
        </Form>
    {:else if tab === 'home'}
        <Form {...SettingController.updateHome.form()} class="max-w-3xl">
            {#snippet children({ errors, processing })}
                <Card class="gap-5 py-5">
                    <div class="px-5">
                        <h3 class="text-base font-medium">Slide hero</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Maksimal 5 slide. Tulis {'{school}'} untuk menyisipkan nama
                            sekolah otomatis.
                        </p>
                    </div>

                    <div class="grid gap-4 px-5">
                        {#each deck as slide, index (index)}
                            <div class="grid gap-3 rounded-lg border p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-sm font-medium">Slide {index + 1}</p>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        onclick={() => removeSlide(index)}
                                    >
                                        <Trash2 class="size-4 text-destructive" />
                                        <span class="sr-only">Hapus slide {index + 1}</span>
                                    </Button>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="grid gap-2 sm:col-span-2">
                                        <Label for={`slide-eyebrow-${index}`}>Label kecil</Label>
                                        <Input
                                            id={`slide-eyebrow-${index}`}
                                            name={`slides[${index}][eyebrow]`}
                                            bind:value={slide.eyebrow}
                                            required
                                            placeholder="Selamat datang di {'{school}'}"
                                        />
                                        <InputError message={errors[`slides.${index}.eyebrow`]} />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for={`slide-title-${index}`}>Judul</Label>
                                        <Input
                                            id={`slide-title-${index}`}
                                            name={`slides[${index}][title]`}
                                            bind:value={slide.title}
                                            required
                                            placeholder="Menyiapkan generasi"
                                        />
                                        <InputError message={errors[`slides.${index}.title`]} />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for={`slide-accent-${index}`}>
                                            Kata beraksen (oranye)
                                        </Label>
                                        <Input
                                            id={`slide-accent-${index}`}
                                            name={`slides[${index}][accent]`}
                                            value={slide.accent ?? ''}
                                            placeholder="berakhlak"
                                        />
                                    </div>
                                    <div class="grid gap-2 sm:col-span-2">
                                        <Label for={`slide-end-${index}`}>Lanjutan judul</Label>
                                        <Input
                                            id={`slide-end-${index}`}
                                            name={`slides[${index}][title_end]`}
                                            value={slide.title_end ?? ''}
                                            placeholder="dan berprestasi"
                                        />
                                    </div>
                                    <div class="grid gap-2 sm:col-span-2">
                                        <Label for={`slide-text-${index}`}>Paragraf</Label>
                                        <textarea
                                            id={`slide-text-${index}`}
                                            name={`slides[${index}][text]`}
                                            bind:value={slide.text}
                                            rows="3"
                                            maxlength="300"
                                            required
                                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                        ></textarea>
                                        <InputError message={errors[`slides.${index}.text`]} />
                                    </div>

                                    <div class="grid gap-2 sm:col-span-2">
                                        <Label for={`slide-image-${index}`}>Gambar latar</Label>
                                        {#if slide.image_url}
                                            <img
                                                src={slide.image_url}
                                                alt=""
                                                class="h-28 w-full rounded-lg object-cover"
                                            />
                                        {/if}
                                        <input
                                            id={`slide-image-${index}`}
                                            type="file"
                                            name={`slides[${index}][image]`}
                                            accept="image/jpeg,image/png,image/webp"
                                            class="text-sm"
                                        />
                                        <input
                                            type="hidden"
                                            name={`slides[${index}][existing_image]`}
                                            value={slide.image ?? ''}
                                        />
                                        <InputError message={errors[`slides.${index}.image`]} />
                                        <p class="text-xs text-muted-foreground">
                                            Kosongkan untuk memakai gambar yang sekarang.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>

                    <div class="px-5">
                        <Button
                            type="button"
                            variant="outline"
                            onclick={addSlide}
                            disabled={deck.length >= 5}
                        >
                            <Plus class="size-4" />
                            Tambah slide
                        </Button>
                    </div>

                    <Separator />

                    <div class="px-5">
                        <h3 class="text-base font-medium">Kartu di bawah hero</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Maksimal 8 kartu.</p>
                    </div>

                    <div class="grid gap-4 px-5">
                        {#each cards as card, index (index)}
                            <div class="grid gap-3 rounded-lg border p-4 sm:grid-cols-[10rem_1fr_auto]">
                                <div class="grid gap-2">
                                    <Label for={`card-icon-${index}`}>Ikon</Label>
                                    <div class="flex items-center gap-2">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-muted">
                                            <Icon name={card.icon} class="size-4" />
                                        </span>
                                        <select
                                            id={`card-icon-${index}`}
                                            name={`highlights[${index}][icon]`}
                                            bind:value={card.icon}
                                            class="h-9 w-full rounded-md border border-input bg-transparent px-2 text-sm shadow-xs"
                                        >
                                            {#each icons as option (option)}
                                                <option value={option}>{option}</option>
                                            {/each}
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <Label for={`card-title-${index}`}>Judul</Label>
                                    <Input
                                        id={`card-title-${index}`}
                                        name={`highlights[${index}][title]`}
                                        bind:value={card.title}
                                        maxlength={60}
                                        required
                                    />
                                    <Label for={`card-text-${index}`} class="mt-1">
                                        Keterangan
                                    </Label>
                                    <textarea
                                        id={`card-text-${index}`}
                                        name={`highlights[${index}][text]`}
                                        bind:value={card.text}
                                        rows="2"
                                        maxlength="160"
                                        required
                                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                    ></textarea>
                                    <InputError message={errors[`highlights.${index}.title`]} />
                                    <InputError message={errors[`highlights.${index}.text`]} />
                                </div>

                                <div class="flex items-start">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        onclick={() => removeCard(index)}
                                    >
                                        <Trash2 class="size-4 text-destructive" />
                                        <span class="sr-only">Hapus kartu {index + 1}</span>
                                    </Button>
                                </div>
                            </div>
                        {/each}
                    </div>

                    <div class="px-5">
                        <Button
                            type="button"
                            variant="outline"
                            onclick={addCard}
                            disabled={cards.length >= 8}
                        >
                            <Plus class="size-4" />
                            Tambah kartu
                        </Button>
                    </div>

                    <Separator />

                    <div class="px-5">
                        <h3 class="text-base font-medium">Poin seksi Tentang</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Maksimal 4 poin.</p>
                    </div>

                    <div class="grid gap-4 px-5">
                        {#each points as point, index (index)}
                            <div class="grid gap-3 rounded-lg border p-4 sm:grid-cols-[10rem_1fr_auto]">
                                <div class="grid gap-2">
                                    <Label for={`point-icon-${index}`}>Ikon</Label>
                                    <div class="flex items-center gap-2">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-muted">
                                            <Icon name={point.icon} class="size-4" />
                                        </span>
                                        <select
                                            id={`point-icon-${index}`}
                                            name={`about_points[${index}][icon]`}
                                            bind:value={point.icon}
                                            class="h-9 w-full rounded-md border border-input bg-transparent px-2 text-sm shadow-xs"
                                        >
                                            {#each icons as option (option)}
                                                <option value={option}>{option}</option>
                                            {/each}
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <Label for={`point-title-${index}`}>Judul</Label>
                                    <Input
                                        id={`point-title-${index}`}
                                        name={`about_points[${index}][title]`}
                                        bind:value={point.title}
                                        maxlength={80}
                                        required
                                    />
                                    <Label for={`point-text-${index}`} class="mt-1">
                                        Keterangan
                                    </Label>
                                    <textarea
                                        id={`point-text-${index}`}
                                        name={`about_points[${index}][text]`}
                                        bind:value={point.text}
                                        rows="2"
                                        maxlength="200"
                                        required
                                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                    ></textarea>
                                    <InputError message={errors[`about_points.${index}.title`]} />
                                    <InputError message={errors[`about_points.${index}.text`]} />
                                </div>

                                <div class="flex items-start">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        onclick={() => removePoint(index)}
                                    >
                                        <Trash2 class="size-4 text-destructive" />
                                        <span class="sr-only">Hapus poin {index + 1}</span>
                                    </Button>
                                </div>
                            </div>
                        {/each}
                    </div>

                    <div class="px-5">
                        <Button
                            type="button"
                            variant="outline"
                            onclick={addPoint}
                            disabled={points.length >= 4}
                        >
                            <Plus class="size-4" />
                            Tambah poin
                        </Button>
                    </div>

                    <Separator />

                    <div class="grid gap-5 px-5 sm:grid-cols-3">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="home_quote">Kutipan</Label>
                            <textarea
                                id="home_quote"
                                name="home_quote"
                                rows="3"
                                maxlength="300"
                                value={values.home_quote ?? ''}
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                            ></textarea>
                            <InputError message={errors.home_quote} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="home_quote_by">Sumber kutipan</Label>
                            <Input
                                id="home_quote_by"
                                name="home_quote_by"
                                value={values.home_quote_by ?? ''}
                                placeholder="Wali murid kelas VIII"
                            />
                            <InputError message={errors.home_quote_by} />
                        </div>
                    </div>

                    <div class="px-5">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Simpan konten beranda'}
                        </Button>
                    </div>
                </Card>
            {/snippet}
        </Form>
    {:else if tab === 'ppdb'}
        <Form {...SettingController.updatePpdb.form()} class="max-w-3xl">
            {#snippet children({ errors, processing })}
                <Card class="gap-5 py-5">
                    <div class="px-5">
                        <h3 class="text-base font-medium">Alur pendaftaran</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Langkah bernomor di halaman PPDB. Maksimal 8.
                        </p>
                    </div>

                    <div class="grid gap-2 px-5">
                        <Label for="ppdb_intro">Kalimat pembuka</Label>
                        <textarea
                            id="ppdb_intro"
                            name="ppdb_intro"
                            rows="2"
                            maxlength="300"
                            value={values.ppdb_intro ?? ''}
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                        ></textarea>
                        <InputError message={errors.ppdb_intro} />
                    </div>

                    <div class="grid gap-4 px-5">
                        {#each steps as step, index (index)}
                            <div class="grid gap-3 rounded-lg border p-4 sm:grid-cols-[1fr_auto]">
                                <div class="grid gap-2">
                                    <Label for={`step-title-${index}`}>
                                        Langkah {index + 1}
                                    </Label>
                                    <Input
                                        id={`step-title-${index}`}
                                        name={`ppdb_steps[${index}][title]`}
                                        bind:value={step.title}
                                        maxlength={80}
                                        required
                                    />
                                    <textarea
                                        name={`ppdb_steps[${index}][text]`}
                                        bind:value={step.text}
                                        rows="2"
                                        maxlength="200"
                                        required
                                        aria-label={`Keterangan langkah ${index + 1}`}
                                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                    ></textarea>
                                    <InputError message={errors[`ppdb_steps.${index}.title`]} />
                                    <InputError message={errors[`ppdb_steps.${index}.text`]} />
                                </div>
                                <div class="flex items-start">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        onclick={() => removeStep(index)}
                                    >
                                        <Trash2 class="size-4 text-destructive" />
                                        <span class="sr-only">Hapus langkah {index + 1}</span>
                                    </Button>
                                </div>
                            </div>
                        {/each}
                    </div>

                    <div class="px-5">
                        <Button
                            type="button"
                            variant="outline"
                            onclick={addStep}
                            disabled={steps.length >= 8}
                        >
                            <Plus class="size-4" />
                            Tambah langkah
                        </Button>
                    </div>

                    <Separator />

                    <div class="px-5">
                        <h3 class="text-base font-medium">Ajakan mendaftar di beranda</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Kotak hijau di bagian bawah beranda.
                        </p>
                    </div>

                    <div class="grid gap-5 px-5">
                        <div class="grid gap-2">
                            <Label for="ppdb_cta_eyebrow">Label kecil</Label>
                            <Input
                                id="ppdb_cta_eyebrow"
                                name="ppdb_cta_eyebrow"
                                value={values.ppdb_cta_eyebrow ?? ''}
                                placeholder="PPDB 2027/2028"
                            />
                            <InputError message={errors.ppdb_cta_eyebrow} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="ppdb_cta_title">Judul</Label>
                            <Input
                                id="ppdb_cta_title"
                                name="ppdb_cta_title"
                                value={values.ppdb_cta_title ?? ''}
                                placeholder="Kuota 96 siswa. Gelombang I tutup 28 Februari."
                            />
                            <InputError message={errors.ppdb_cta_title} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="ppdb_cta_text">Paragraf</Label>
                            <textarea
                                id="ppdb_cta_text"
                                name="ppdb_cta_text"
                                rows="3"
                                maxlength="300"
                                value={values.ppdb_cta_text ?? ''}
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                            ></textarea>
                            <InputError message={errors.ppdb_cta_text} />
                        </div>
                    </div>

                    <div class="px-5">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Simpan konten PPDB'}
                        </Button>
                    </div>
                </Card>
            {/snippet}
        </Form>
    {:else}
        <Form {...SettingController.updateBranding.form()} class="max-w-3xl">
            {#snippet children({ errors, processing })}
                <Card class="gap-5 py-5">
                    <div class="grid gap-5 px-5 sm:grid-cols-2">
                        <div>
                            <ImageUpload
                                id="logo"
                                label="Logo sekolah"
                                hint="PNG transparan, sisi terpanjang 512 px."
                                current={images.logo ?? ''}
                            />
                            <InputError class="mt-2" message={errors.logo} />
                        </div>
                        <div>
                            <ImageUpload
                                id="favicon"
                                label="Favicon"
                                hint="PNG 96×96 px."
                                current={images.favicon ?? ''}
                            />
                            <InputError class="mt-2" message={errors.favicon} />
                        </div>
                        <div>
                            <ImageUpload
                                id="hero_image"
                                label="Gambar hero beranda"
                                hint="JPG lanskap, minimal 1600 px."
                                current={images.hero_image ?? ''}
                            />
                            <InputError class="mt-2" message={errors.hero_image} />
                        </div>
                        <div>
                            <ImageUpload
                                id="og_image"
                                label="Gambar berbagi (OG image)"
                                hint="JPG 1200×630 px."
                                current={images.og_image ?? ''}
                            />
                            <InputError class="mt-2" message={errors.og_image} />
                        </div>
                        <div>
                            <ImageUpload
                                id="about_hero_image"
                                label="Banner halaman Tentang Kami"
                                hint="JPG lanskap, minimal 1600 px."
                                current={images.about_hero_image ?? ''}
                            />
                            <InputError class="mt-2" message={errors.about_hero_image} />
                        </div>
                        <div>
                            <ImageUpload
                                id="about_photo"
                                label="Foto gedung sekolah"
                                hint="Tampil di seksi profil singkat."
                                current={images.about_photo ?? ''}
                            />
                            <InputError class="mt-2" message={errors.about_photo} />
                        </div>
                    </div>
                    <div class="px-5">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Mengunggah…' : 'Simpan branding'}
                        </Button>
                    </div>
                </Card>
            {/snippet}
        </Form>
    {/if}
</div>
