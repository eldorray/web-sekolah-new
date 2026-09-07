<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Guru', href: '/admin/teachers' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import { untrack } from 'svelte';
    import TeacherController from '@/actions/App/Http/Controllers/Admin/TeacherController';
    import ImageUpload from '@/components/admin/ImageUpload.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type Teacher = {
        id: number;
        name: string;
        email: string;
        position: string | null;
        phone: string | null;
        bio: string | null;
        instagram: string | null;
        facebook: string | null;
        role: string;
        is_active: boolean;
        photo: string | null;
    };

    let { teacher = null }: { teacher?: Teacher | null } = $props();

    const editing = $derived(teacher !== null);
    const action = $derived(
        teacher === null
            ? TeacherController.store.form()
            : TeacherController.update.form({ teacher: teacher.id }),
    );

    let bio = $state(untrack(() => teacher?.bio ?? ''));
    let isActive = $state(untrack(() => teacher?.is_active ?? true));
</script>

<AppHead title={editing ? 'Ubah data guru' : 'Tambah guru'} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={editing ? 'Ubah data guru' : 'Tambah guru'}
            description="Akun guru dibuat manual oleh admin, tanpa pendaftaran mandiri."
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/teachers" class={props.class}>
                    <ArrowLeft class="size-4" />
                    Kembali
                </Link>
            {/snippet}
        </Button>
    </div>

    <Form {...action} class="grid gap-6 lg:grid-cols-3">
        {#snippet children({ errors, processing })}
            <Card class="gap-5 py-5 lg:col-span-2">
                <div class="grid gap-5 px-5 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="name">Nama lengkap dengan gelar</Label>
                        <Input id="name" name="name" value={teacher?.name ?? ''} required />
                        <InputError message={errors.name} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" value={teacher?.email ?? ''} required />
                        <InputError message={errors.email} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">Telepon</Label>
                        <Input id="phone" name="phone" type="tel" value={teacher?.phone ?? ''} placeholder="08xx xxxx xxxx" />
                        <InputError message={errors.phone} />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="position">Jabatan</Label>
                        <Input id="position" name="position" value={teacher?.position ?? ''} required placeholder="Mis. Guru IPA · Pembina Riset" />
                        <InputError message={errors.position} />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="bio">Bio singkat</Label>
                        <textarea
                            id="bio"
                            name="bio"
                            bind:value={bio}
                            rows="3"
                            maxlength="240"
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                            placeholder="Tampil di kartu guru pada situs publik"
                        ></textarea>
                        <InputError message={errors.bio} />
                        <p class="text-xs text-muted-foreground">{bio.length}/240 karakter</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="instagram">Instagram</Label>
                        <Input id="instagram" name="instagram" value={teacher?.instagram ?? ''} placeholder="https://instagram.com/..." />
                        <InputError message={errors.instagram} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="facebook">Facebook</Label>
                        <Input id="facebook" name="facebook" value={teacher?.facebook ?? ''} placeholder="https://facebook.com/..." />
                        <InputError message={errors.facebook} />
                    </div>
                </div>
            </Card>

            <div class="flex flex-col gap-4">
                <Card class="gap-5 py-5">
                    <div class="grid gap-2 px-5">
                        <Label for="role">Peran akun</Label>
                        <select
                            id="role"
                            name="role"
                            value={teacher?.role ?? 'guru'}
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                        >
                            <option value="guru">Guru</option>
                            <option value="admin">Admin</option>
                        </select>
                        <InputError message={errors.role} />
                        <p class="text-xs text-muted-foreground">
                            Admin membuka seluruh panel. Guru hanya berita miliknya
                            sendiri dan profilnya.
                        </p>
                    </div>

                    <div class="grid gap-2 px-5">
                        <Label for="password">
                            {editing ? 'Password baru (opsional)' : 'Password awal'}
                        </Label>
                        <PasswordInput id="password" name="password" required={!editing} />
                        <InputError message={errors.password} />
                        <p class="text-xs text-muted-foreground">
                            Minimal 8 karakter.
                            {editing ? ' Kosongkan bila tidak diubah.' : ''}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 px-5">
                        <input type="hidden" name="is_active" value={isActive ? '1' : '0'} />
                        <Checkbox id="is_active" bind:checked={isActive} />
                        <Label for="is_active">Tampilkan di halaman Tim Guru</Label>
                    </div>
                </Card>

                <Card class="gap-4 py-5">
                    <div class="px-5">
                        <ImageUpload
                            id="photo"
                            label="Foto guru"
                            hint="Potret tegak, JPG atau PNG, maksimal 2 MB."
                            current={teacher?.photo ?? ''}
                        />
                        <InputError class="mt-2" message={errors.photo} />
                    </div>
                </Card>

                <Button type="submit" class="w-full" disabled={processing}>
                    {processing
                        ? 'Menyimpan…'
                        : editing
                          ? 'Simpan perubahan'
                          : 'Simpan guru'}
                </Button>
            </div>
        {/snippet}
    </Form>
</div>
