<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Pengguna', href: '/admin/users' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import { untrack } from 'svelte';
    import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type UserRow = {
        id: number;
        name: string;
        email: string;
        role: string;
        is_active: boolean;
    };

    let { user = null }: { user?: UserRow | null } = $props();

    const editing = $derived(user !== null);
    const action = $derived(
        user === null
            ? UserController.store.form()
            : UserController.update.form({ user: user.id }),
    );

    let isActive = $state(untrack(() => user?.is_active ?? true));
</script>

<AppHead title={editing ? 'Ubah pengguna' : 'Tambah pengguna'} />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title={editing ? 'Ubah pengguna' : 'Tambah pengguna'}
            description="Password diberikan admin, lalu diganti pengguna lewat pengaturan akun."
        />
        <Button variant="outline" asChild>
            {#snippet children(props)}
                <Link {...props} href="/admin/users" class={props.class}>
                    <ArrowLeft class="size-4" />
                    Kembali
                </Link>
            {/snippet}
        </Button>
    </div>

    <Form {...action} class="max-w-2xl">
        {#snippet children({ errors, processing })}
            <Card class="gap-5 py-5">
                <div class="grid gap-5 px-5 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="name">Nama</Label>
                        <Input id="name" name="name" value={user?.name ?? ''} required />
                        <InputError message={errors.name} />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" value={user?.email ?? ''} required />
                        <InputError message={errors.email} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="role">Peran</Label>
                        <select
                            id="role"
                            name="role"
                            value={user?.role ?? 'guru'}
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                        >
                            <option value="guru">Guru</option>
                            <option value="admin">Admin</option>
                        </select>
                        <InputError message={errors.role} />
                    </div>
                    <div class="flex items-end gap-3">
                        <input type="hidden" name="is_active" value={isActive ? '1' : '0'} />
                        <Checkbox id="is_active" bind:checked={isActive} />
                        <Label for="is_active">Akun aktif</Label>
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="password">
                            {editing ? 'Password baru (opsional)' : 'Password awal'}
                        </Label>
                        <PasswordInput id="password" name="password" required={!editing} />
                        <InputError message={errors.password} />
                        <p class="text-xs text-muted-foreground">
                            Minimal 8 karakter. Jangan pakai password bersama antar akun.
                        </p>
                    </div>
                </div>

                <div class="px-5">
                    <Button type="submit" disabled={processing}>
                        {processing
                            ? 'Menyimpan…'
                            : editing
                              ? 'Simpan perubahan'
                              : 'Buat akun'}
                    </Button>
                </div>
            </Card>
        {/snippet}
    </Form>
</div>
