<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'PPDB', href: '/admin/ppdb' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import FileText from '@lucide/svelte/icons/file-text';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Label } from '@/components/ui/label';
    import {
        statusBadgeClass,
        statusLabels,
        type PpdbStatus,
    } from '@/lib/admin';
    import PpdbController from '@/actions/App/Http/Controllers/Admin/PpdbController';

    type Registration = {
        number: string;
        full_name: string;
        nickname: string | null;
        gender: string;
        birthplace: string;
        birthdate: string | null;
        previous_school: string;
        address: string;
        father_name: string;
        mother_name: string;
        parent_phone: string;
        parent_email: string;
        grade_target: string;
        created_at: string | null;
        status: PpdbStatus;
        notes: string | null;
        has_kk: boolean;
        has_birth_certificate: boolean;
    };

    let {
        number = '',
        registration,
    }: {
        number?: string;
        registration: Registration;
    } = $props();

    const row = $derived(registration);

    const documents = $derived([
        { label: 'Kartu keluarga', type: 'kk', available: row.has_kk },
        {
            label: 'Akta kelahiran',
            type: 'akte',
            available: row.has_birth_certificate,
        },
    ]);

    const identity = $derived([
        { label: 'Nama lengkap', value: row.full_name },
        { label: 'Nama panggilan', value: row.nickname },
        { label: 'Jenis kelamin', value: row.gender },
        { label: 'Tempat lahir', value: row.birthplace },
        { label: 'Tanggal lahir', value: row.birthdate },
        { label: 'Asal sekolah', value: row.previous_school },
        { label: 'Kelas dituju', value: row.grade_target },
        { label: 'Alamat', value: row.address },
    ]);

    const parents = $derived([
        { label: 'Nama ayah', value: row.father_name },
        { label: 'Nama ibu', value: row.mother_name },
        { label: 'Nomor WhatsApp', value: row.parent_phone },
        { label: 'Email', value: row.parent_email },
    ]);
</script>

<AppHead title={row.number} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={row.full_name}
            description="{row.number} · masuk {row.created_at}"
        />
        <div class="flex items-center gap-3">
            <Badge variant="outline" class={statusBadgeClass[row.status]}>
                {statusLabels[row.status]}
            </Badge>
            <Button variant="outline" asChild>
                {#snippet children(props)}
                    <Link {...props} href="/admin/ppdb" class={props.class}>
                        <ArrowLeft class="size-4" />
                        Kembali
                    </Link>
                {/snippet}
            </Button>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="flex flex-col gap-4 lg:col-span-2">
            <Card class="gap-4 py-5">
                <h3 class="px-5 text-base font-medium">Data calon siswa</h3>
                <dl class="grid gap-4 px-5 sm:grid-cols-2">
                    {#each identity as field (field.label)}
                        <div>
                            <dt class="text-xs text-muted-foreground">{field.label}</dt>
                            <dd class="mt-0.5 text-sm font-medium">{field.value}</dd>
                        </div>
                    {/each}
                </dl>
            </Card>

            <Card class="gap-4 py-5">
                <h3 class="px-5 text-base font-medium">Orang tua / wali</h3>
                <dl class="grid gap-4 px-5 sm:grid-cols-2">
                    {#each parents as field (field.label)}
                        <div>
                            <dt class="text-xs text-muted-foreground">{field.label}</dt>
                            <dd class="mt-0.5 text-sm font-medium">{field.value}</dd>
                        </div>
                    {/each}
                </dl>
            </Card>

            <Card class="gap-4 py-5">
                <div class="px-5">
                    <h3 class="text-base font-medium">Dokumen</h3>
                    <p class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                        <ShieldCheck class="size-3.5" />
                        Disimpan di disk privat. Hanya admin yang bisa mengunduh.
                    </p>
                </div>
                <div class="grid gap-3 px-5 sm:grid-cols-2">
                    {#each documents as document (document.type)}
                        <div class="flex items-center justify-between gap-3 rounded-lg border p-3">
                            <div class="flex items-center gap-3">
                                <FileText class="size-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">{document.label}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {document.available ? 'Terunggah' : 'Belum ada berkas'}
                                    </p>
                                </div>
                            </div>
                            {#if document.available}
                                <Button variant="secondary" size="sm" asChild>
                                    {#snippet children(props)}
                                        <a
                                            {...props}
                                            href={PpdbController.document.url({
                                                registration: row.number,
                                                type: document.type,
                                            })}
                                            class={props.class}
                                        >
                                            Unduh
                                        </a>
                                    {/snippet}
                                </Button>
                            {/if}
                        </div>
                    {/each}
                </div>
            </Card>
        </div>

        <Card class="h-fit gap-5 py-5">
            <h3 class="px-5 text-base font-medium">Verifikasi</h3>
            <Form
                {...PpdbController.update.form({ registration: row.number })}
                class="flex flex-col gap-5"
                options={{ preserveScroll: true }}
            >
                {#snippet children({ errors, processing })}
                    <div class="grid gap-2 px-5">
                        <Label for="status">Status pendaftaran</Label>
                        <select
                            id="status"
                            name="status"
                            value={row.status}
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                        >
                            <option value="pending">Menunggu</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                        <InputError message={errors.status} />
                    </div>

                    <div class="grid gap-2 px-5">
                        <Label for="notes">Catatan panitia</Label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="5"
                            value={row.notes ?? ''}
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                            placeholder="Mis. berkas kurang akta, sudah dihubungi 4 Sep"
                        ></textarea>
                        <InputError message={errors.notes} />
                        <p class="text-xs text-muted-foreground">
                            Catatan tersimpan bersama status dan tercatat di audit log.
                        </p>
                    </div>

                    <div class="px-5">
                        <Button type="submit" class="w-full" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Simpan status'}
                        </Button>
                    </div>
                {/snippet}
            </Form>
        </Card>
    </div>
</div>
