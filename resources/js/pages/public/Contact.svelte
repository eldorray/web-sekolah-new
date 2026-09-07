<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import Clock from '@lucide/svelte/icons/clock';
    import Mail from '@lucide/svelte/icons/mail';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import PhoneCall from '@lucide/svelte/icons/phone-call';
    import Send from '@lucide/svelte/icons/send';
    import ContactController from '@/actions/App/Http/Controllers/Public/ContactController';
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import InputError from '@/components/InputError.svelte';
    import PageHero from '@/components/public/PageHero.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';

    let { map_embed = '' }: { map_embed?: string | null } = $props();

    const school = $derived(page.props.school ?? {});
    const flash = $derived(page.flash ?? {});
    const startedAt = Math.floor(Date.now() / 1000);

    const details = $derived([
        { icon: MapPin, label: 'Alamat', value: school.address },
        {
            icon: PhoneCall,
            label: 'Telepon',
            value: `${school.phone ?? ''} · WA ${school.whatsapp ?? ''}`,
        },
        { icon: Mail, label: 'Email', value: school.email },
        { icon: Clock, label: 'Jam layanan', value: school.hours },
    ]);
</script>

<AppHead title="Kontak" />
<PageHero
    title="Kontak"
    crumb="Kontak"
    image="https://picsum.photos/seed/dh-contact-hero/1600/500"
/>

<section class="bg-white py-20">
    <div class="shell grid gap-10 lg:grid-cols-3">
        <div class="space-y-4">
            {#each details as detail, detailIndex (detail.label)}
                <div
                    use:reveal={{ delay: detailIndex * 80 }}
                    class="flex gap-4 rounded-2xl bg-mist p-5"
                >
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand text-white">
                        <detail.icon class="size-5" />
                    </span>
                    <div>
                        <p class="font-display text-xs font-bold text-ink-soft uppercase">
                            {detail.label}
                        </p>
                        <p class="mt-1 text-sm leading-relaxed text-ink">
                            {detail.value ?? '—'}
                        </p>
                    </div>
                </div>
            {/each}
        </div>

        <div class="lg:col-span-2">
            <SectionHead
                align="left"
                eyebrow="Kirim pesan"
                title="Ada yang ingin"
                accent="ditanyakan?"
                text="Pesan masuk ke panel admin dan dijawab pada hari kerja berikutnya."
            />

            {#if flash.contact_sent}
                <p class="mt-8 rounded-2xl bg-brand-soft p-6 text-sm text-brand-deep" role="status">
                    Pesan terkirim. Panitia menghubungi Anda lewat email atau
                    WhatsApp pada hari kerja berikutnya.
                </p>
            {/if}

            <Form
                {...ContactController.storeMessage.form()}
                resetOnSuccess
                class="mt-8 grid gap-5 sm:grid-cols-2"
            >
                {#snippet children({ errors, processing })}
                    <input type="hidden" name="form_started_at" value={startedAt} />
                    <label class="hidden" aria-hidden="true">
                        Jangan isi kolom ini
                        <input type="text" name="website" tabindex="-1" autocomplete="off" />
                    </label>

                    <div>
                        <label class="field-label" for="nama">Nama</label>
                        <input id="nama" name="name" class="field" required placeholder="Nama lengkap" />
                        <InputError message={errors.name} />
                    </div>
                    <div>
                        <label class="field-label" for="email">Email</label>
                        <input id="email" name="email" type="email" class="field" required placeholder="nama@email.com" />
                        <InputError message={errors.email} />
                    </div>
                    <div>
                        <label class="field-label" for="telepon">Telepon</label>
                        <input id="telepon" name="phone" type="tel" class="field" placeholder="08xx xxxx xxxx" />
                        <InputError message={errors.phone} />
                    </div>
                    <div>
                        <label class="field-label" for="subjek">Subjek</label>
                        <input id="subjek" name="subject" class="field" required placeholder="Pertanyaan tentang PPDB" />
                        <InputError message={errors.subject} />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label" for="pesan">Pesan</label>
                        <textarea id="pesan" name="message" rows="5" class="field" required placeholder="Tulis pesan Anda"></textarea>
                        <InputError message={errors.message} />
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-sun" disabled={processing}>
                            {processing ? 'Mengirim…' : 'Kirim pesan'}
                            <Send class="size-4" />
                        </button>
                    </div>
                {/snippet}
            </Form>
        </div>
    </div>
</section>

<section class="bg-mist/60 py-20">
    <div class="shell grid gap-10 lg:grid-cols-2">
        <div>
            <SectionHead
                align="left"
                eyebrow="Kunjungan sekolah"
                title="Ingin melihat sekolah"
                accent="langsung?"
                text="Ajukan jadwal kunjungan, panitia mengonfirmasi ketersediaan hari dan pendamping."
            />

            {#if flash.visit_sent}
                <p class="mt-8 rounded-2xl bg-white p-6 text-sm text-brand-deep" role="status">
                    Permintaan kunjungan tercatat. Konfirmasi jadwal dikirim ke
                    email Anda.
                </p>
            {/if}

            <Form
                {...ContactController.storeVisit.form()}
                resetOnSuccess
                class="mt-8 grid gap-5 sm:grid-cols-2"
            >
                {#snippet children({ errors, processing })}
                    <input type="hidden" name="form_started_at" value={startedAt} />
                    <label class="hidden" aria-hidden="true">
                        Jangan isi kolom ini
                        <input type="text" name="website" tabindex="-1" autocomplete="off" />
                    </label>

                    <div>
                        <label class="field-label" for="v-nama">Nama</label>
                        <input id="v-nama" name="name" class="field" required placeholder="Nama pemohon" />
                        <InputError message={errors.name} />
                    </div>
                    <div>
                        <label class="field-label" for="v-email">Email</label>
                        <input id="v-email" name="email" type="email" class="field" required placeholder="nama@email.com" />
                        <InputError message={errors.email} />
                    </div>
                    <div>
                        <label class="field-label" for="v-telepon">Telepon</label>
                        <input id="v-telepon" name="phone" type="tel" class="field" placeholder="08xx xxxx xxxx" />
                        <InputError message={errors.phone} />
                    </div>
                    <div>
                        <label class="field-label" for="v-tanggal">Tanggal kunjungan</label>
                        <input id="v-tanggal" name="visit_date" type="date" class="field" required />
                        <InputError message={errors.visit_date} />
                    </div>
                    <div>
                        <label class="field-label" for="v-jumlah">Jumlah peserta</label>
                        <input id="v-jumlah" name="participants" type="number" min="1" max="200" class="field" required value="2" />
                        <InputError message={errors.participants} />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label" for="v-tujuan">Tujuan kunjungan</label>
                        <textarea id="v-tujuan" name="purpose" rows="3" class="field" required placeholder="Mis. melihat fasilitas sebelum mendaftar"></textarea>
                        <InputError message={errors.purpose} />
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-brand" disabled={processing}>
                            {processing ? 'Mengirim…' : 'Ajukan jadwal'}
                        </button>
                    </div>
                {/snippet}
            </Form>
        </div>

        {#if map_embed}
            <div
                use:reveal={{ y: 20 }}
                class="overflow-hidden rounded-2xl bg-white p-2 shadow-lg shadow-ink/5"
            >
                <iframe
                    title="Peta lokasi {school.school_name ?? 'sekolah'}"
                    src={map_embed}
                    loading="lazy"
                    class="h-full min-h-[420px] w-full rounded-xl border-0"
                ></iframe>
            </div>
        {/if}
    </div>
</section>
