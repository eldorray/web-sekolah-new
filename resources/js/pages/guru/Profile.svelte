<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Guru', href: '/guru' },
            { title: 'Profil', href: '/guru/profile' },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import ProfileController from '@/actions/App/Http/Controllers/Guru/ProfileController';
    import ImageUpload from '@/components/admin/ImageUpload.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type Profile = {
        name: string;
        position: string | null;
        phone: string | null;
        bio: string | null;
        instagram: string | null;
        facebook: string | null;
        photo: string | null;
    };

    let { profile }: { profile: Profile } = $props();

    let bio = $state(untrack(() => profile.bio ?? ''));
</script>

<AppHead title="Profil saya" />

<div class="flex flex-col gap-6 p-4">
    <Heading
        title="Profil saya"
        description="Data ini tampil di halaman Tim Guru situs publik."
    />

    <Form
        {...ProfileController.update.form()}
        class="grid max-w-4xl gap-6 lg:grid-cols-3"
    >
        {#snippet children({ errors, processing })}
            <Card class="gap-5 py-5 lg:col-span-2">
                <div class="grid gap-5 px-5 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="name">Nama lengkap dengan gelar</Label>
                        <Input id="name" name="name" value={profile.name} required />
                        <InputError message={errors.name} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="position">Jabatan</Label>
                        <Input
                            id="position"
                            name="position"
                            value={profile.position ?? ''}
                            placeholder="Mis. Guru IPA"
                        />
                        <InputError message={errors.position} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">Telepon</Label>
                        <Input id="phone" name="phone" type="tel" value={profile.phone ?? ''} />
                        <InputError message={errors.phone} />
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
                        ></textarea>
                        <InputError message={errors.bio} />
                        <p class="text-xs text-muted-foreground">{bio.length}/240 karakter</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="instagram">Instagram</Label>
                        <Input id="instagram" name="instagram" value={profile.instagram ?? ''} />
                        <InputError message={errors.instagram} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="facebook">Facebook</Label>
                        <Input id="facebook" name="facebook" value={profile.facebook ?? ''} />
                        <InputError message={errors.facebook} />
                    </div>
                </div>

                <div class="px-5">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Menyimpan…' : 'Simpan profil'}
                    </Button>
                </div>
            </Card>

            <div class="flex flex-col gap-4">
                <Card class="gap-4 py-5">
                    <div class="px-5">
                        <ImageUpload
                            id="photo"
                            label="Foto profil"
                            hint="Potret tegak, JPG atau PNG."
                            current={profile.photo ?? ''}
                        />
                        <InputError class="mt-2" message={errors.photo} />
                    </div>
                </Card>

                <Card class="gap-2 py-5">
                    <div class="px-5">
                        <p class="text-sm font-medium">Email & password</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Ganti email, password, dan 2FA lewat pengaturan akun.
                        </p>
                        <Link
                            href="/settings/profile"
                            class="mt-3 inline-block text-sm font-medium text-brand hover:underline"
                        >
                            Buka pengaturan akun
                        </Link>
                    </div>
                </Card>
            </div>
        {/snippet}
    </Form>
</div>
