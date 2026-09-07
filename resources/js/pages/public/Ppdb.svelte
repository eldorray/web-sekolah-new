<script lang="ts">
    import { Form, Link, page } from '@inertiajs/svelte';
    import CircleCheckBig from '@lucide/svelte/icons/circle-check-big';
    import Upload from '@lucide/svelte/icons/upload';
    import PpdbController from '@/actions/App/Http/Controllers/Public/PpdbController';
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import InputError from '@/components/InputError.svelte';
    import PageHero from '@/components/public/PageHero.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';
    import { ppdbSteps } from '@/lib/site';

    let {
        steps = [],
        intro = '',
    }: {
        steps?: Array<{ title: string; text: string }>;
        intro?: string | null;
    } = $props();

    const flow = $derived(steps.length > 0 ? steps : ppdbSteps);

    const school = $derived(page.props.school ?? {});
    const registrationNumber = $derived(page.flash?.registration_number ?? '');
    const startedAt = Math.floor(Date.now() / 1000);

    let kkName = $state('');
    let akteName = $state('');

    function pickName(event: Event): string {
        const input = event.currentTarget as HTMLInputElement;

        return input.files?.[0]?.name ?? '';
    }
</script>

<AppHead title="PPDB" />
<PageHero
    title="Pendaftaran Siswa Baru"
    crumb="PPDB"
    image="https://picsum.photos/seed/dh-ppdb-hero/1600/500"
/>

<!-- Alur pendaftaran: a real sequence, so the steps are numbered. -->
<section class="bg-white py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Alur pendaftaran"
            title="{flow.length} langkah"
            accent="sampai diterima"
            text={intro ||
                'Seluruh proses memakan waktu sekitar dua pekan sejak formulir dikirim.'}
        />
        <ol class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {#each flow as step, index (step.title)}
                <li
                    use:reveal={{ delay: index * 90 }}
                    class="relative rounded-2xl border border-brand-soft p-6"
                >
                    <span class="grid size-11 place-items-center rounded-xl bg-sun font-display text-lg font-extrabold text-white">
                        {index + 1}
                    </span>
                    <h3 class="mt-4 font-display text-base font-bold text-ink">
                        {step.title}
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                        {step.text}
                    </p>
                </li>
            {/each}
        </ol>
    </div>
</section>

<section class="bg-mist/60 py-20">
    <div class="shell max-w-4xl">
        {#if registrationNumber}
            <div
                use:reveal={{ y: 20 }}
                class="rounded-3xl bg-white p-10 text-center shadow-xl shadow-ink/10"
                role="status"
            >
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-brand text-white">
                    <CircleCheckBig class="size-8" />
                </span>
                <h2 class="mt-6 font-display text-2xl font-extrabold text-ink">
                    Formulir terkirim
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-soft">
                    Simpan nomor pendaftaran berikut. Nomor ini dipakai saat
                    verifikasi berkas dan wawancara.
                </p>
                <p class="mx-auto mt-6 w-fit rounded-2xl bg-sun-soft px-8 py-4 font-display text-2xl font-extrabold tracking-wider text-sun-dark">
                    {registrationNumber}
                </p>
                <p class="mt-6 text-sm text-ink-soft">
                    Panitia menghubungi Anda di {school.whatsapp ?? 'nomor yang Anda isi'}
                    dalam dua hari kerja.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <Link href="/" class="btn-brand">Kembali ke beranda</Link>
                    <Link href="/ppdb" class="btn-sun">Daftarkan anak lain</Link>
                </div>
            </div>
        {:else}
            <Form
                {...PpdbController.store.form()}
                class="rounded-3xl bg-white p-8 shadow-xl shadow-ink/10 sm:p-10"
            >
                {#snippet children({ errors, processing })}
                <input type="hidden" name="form_started_at" value={startedAt} />
                <label class="hidden" aria-hidden="true">
                    Jangan isi kolom ini
                    <input type="text" name="website" tabindex="-1" autocomplete="off" />
                </label>

                <SectionHead
                    align="left"
                    eyebrow="Formulir online"
                    title="Data calon siswa"
                    accent="dan orang tua"
                />

                <fieldset use:reveal class="mt-8">
                    <legend class="font-display text-sm font-bold text-brand uppercase">
                        A. Calon siswa
                    </legend>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="field-label" for="full_name">Nama lengkap</label>
                            <input id="full_name" name="full_name" class="field" required placeholder="Sesuai akta kelahiran" />
                        </div>
                        <div>
                            <label class="field-label" for="nickname">Nama panggilan</label>
                            <input id="nickname" name="nickname" class="field" placeholder="Nama panggilan" />
                        </div>
                        <div>
                            <label class="field-label" for="gender">Jenis kelamin</label>
                            <select id="gender" name="gender" class="field" required>
                                <option value="">Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="birthplace">Tempat lahir</label>
                            <input id="birthplace" name="birthplace" class="field" required placeholder="Kota kelahiran" />
                        </div>
                        <div>
                            <label class="field-label" for="birthdate">Tanggal lahir</label>
                            <input id="birthdate" name="birthdate" type="date" class="field" required />
                        </div>
                        <div>
                            <label class="field-label" for="previous_school">Asal sekolah</label>
                            <input id="previous_school" name="previous_school" class="field" required placeholder="MI / SD asal" />
                        </div>
                        <div>
                            <label class="field-label" for="grade_target">Kelas dituju</label>
                            <select id="grade_target" name="grade_target" class="field" required>
                                <option value="7">Kelas VII</option>
                                <option value="8">Kelas VIII (pindahan)</option>
                                <option value="9">Kelas IX (pindahan)</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label" for="address">Alamat rumah</label>
                            <textarea id="address" name="address" rows="3" class="field" required placeholder="Jalan, RT/RW, kelurahan, kota"></textarea>
                        </div>
                    </div>
                    {#each ['full_name', 'gender', 'birthdate', 'previous_school', 'address', 'grade_target'] as field (field)}
                        <InputError class="mt-2" message={errors[field]} />
                    {/each}
                </fieldset>

                <fieldset use:reveal class="mt-10">
                    <legend class="font-display text-sm font-bold text-brand uppercase">
                        B. Orang tua / wali
                    </legend>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="field-label" for="father_name">Nama ayah</label>
                            <input id="father_name" name="father_name" class="field" required placeholder="Nama ayah" />
                        </div>
                        <div>
                            <label class="field-label" for="mother_name">Nama ibu</label>
                            <input id="mother_name" name="mother_name" class="field" required placeholder="Nama ibu" />
                        </div>
                        <div>
                            <label class="field-label" for="parent_phone">Nomor WhatsApp</label>
                            <input id="parent_phone" name="parent_phone" type="tel" class="field" required placeholder="08xx xxxx xxxx" />
                        </div>
                        <div>
                            <label class="field-label" for="parent_email">Email</label>
                            <input id="parent_email" name="parent_email" type="email" class="field" required placeholder="nama@email.com" />
                        </div>
                    </div>
                    {#each ['father_name', 'mother_name', 'parent_phone', 'parent_email'] as field (field)}
                        <InputError class="mt-2" message={errors[field]} />
                    {/each}
                </fieldset>

                <fieldset use:reveal class="mt-10">
                    <legend class="font-display text-sm font-bold text-brand uppercase">
                        C. Dokumen
                    </legend>
                    <p class="mt-2 text-xs text-ink-soft">
                        Kartu keluarga dalam PDF, akta kelahiran dalam JPG atau
                        PNG. Maksimal 2 MB per berkas.
                    </p>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <label class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-brand-soft p-6 text-center transition hover:border-brand hover:bg-mist">
                            <Upload class="size-6 text-brand" />
                            <span class="font-display text-sm font-bold text-ink">
                                Kartu keluarga (PDF)
                            </span>
                            <span class="text-xs text-ink-soft">
                                {kkName || 'Belum ada berkas dipilih'}
                            </span>
                            <input
                                type="file"
                                accept="application/pdf"
                                name="kk_file"
                                class="sr-only"
                                required
                                onchange={(event) => (kkName = pickName(event))}
                            />
                        </label>
                        <label class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-brand-soft p-6 text-center transition hover:border-brand hover:bg-mist">
                            <Upload class="size-6 text-brand" />
                            <span class="font-display text-sm font-bold text-ink">
                                Akta kelahiran (JPG/PNG)
                            </span>
                            <span class="text-xs text-ink-soft">
                                {akteName || 'Belum ada berkas dipilih'}
                            </span>
                            <input
                                type="file"
                                accept="image/jpeg,image/png"
                                name="birth_certificate_file"
                                class="sr-only"
                                required
                                onchange={(event) => (akteName = pickName(event))}
                            />
                        </label>
                    </div>
                    <InputError class="mt-2" message={errors.kk_file} />
                    <InputError class="mt-1" message={errors.birth_certificate_file} />
                    <InputError class="mt-1" message={errors.website} />
                    <InputError class="mt-1" message={errors.form_started_at} />
                </fieldset>

                <label class="mt-8 flex items-start gap-3 text-sm text-ink-soft">
                    <input type="checkbox" required class="mt-0.5 size-4 rounded border-brand-soft accent-brand" />
                    Saya menyatakan data di atas benar dan bersedia dihubungi
                    panitia untuk verifikasi.
                </label>

                <button
                    type="submit"
                    class="btn-sun mt-8 w-full justify-center"
                    disabled={processing}
                >
                    {processing ? 'Mengirim…' : 'Kirim formulir pendaftaran'}
                </button>
                {/snippet}
            </Form>
        {/if}
    </div>
</section>
