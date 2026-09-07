<script lang="ts">
    import Mail from '@lucide/svelte/icons/mail';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import PhoneCall from '@lucide/svelte/icons/phone-call';
    import { page } from '@inertiajs/svelte';
    import SocialIcon from '@/components/public/SocialIcon.svelte';
    import { socials } from '@/lib/site';

    const school = $derived(page.props.school ?? {});
</script>

<div class="hidden bg-brand-dark text-white lg:block">
    <div class="shell flex items-center justify-between gap-6">
        <div class="-mx-5 flex items-center gap-3 bg-sun px-5 py-2.5 sm:-mx-8 sm:px-8">
            <span class="font-display text-xs font-bold tracking-wide">
                Ikuti kami
            </span>
            {#each socials as social (social.label)}
                <a
                    href={(school[social.key] as string) || social.href}
                    target="_blank"
                    rel="noreferrer"
                    aria-label={social.label}
                    class="grid size-7 place-items-center rounded-full bg-white/20 text-white transition hover:bg-white hover:text-sun focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                >
                    <SocialIcon name={social.icon} class="size-3.5" />
                </a>
            {/each}
        </div>

        <dl class="flex items-center gap-7 py-2.5 text-xs">
            <div class="flex items-center gap-2">
                <MapPin class="size-4 text-sun" />
                <dt class="sr-only">Alamat</dt>
                <dd>{school.address ?? '—'}</dd>
            </div>
            <div class="flex items-center gap-2">
                <Mail class="size-4 text-sun" />
                <dt class="sr-only">Email</dt>
                <dd>
                    <a class="hover:underline" href="mailto:{school.email ?? ''}">
                        {school.email ?? '—'}
                    </a>
                </dd>
            </div>
            <div class="flex items-center gap-2">
                <PhoneCall class="size-4 text-sun" />
                <dt class="sr-only">Telepon</dt>
                <dd>{school.phone ?? '—'}</dd>
            </div>
        </dl>
    </div>
</div>
