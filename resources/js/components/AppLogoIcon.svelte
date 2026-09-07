<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';

    /**
     * School mark used by the auth pages, panel sidebar and header.
     *
     * Uses the logo uploaded in Pengaturan → Branding when there is one, and
     * falls back to a neutral icon — never a framework logo.
     */
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();

    const logo = $derived(page.props.branding?.logo as string | undefined);
    const school = $derived(
        (page.props.school?.school_name as string | undefined) ?? 'Sekolah',
    );
</script>

{#if logo}
    <img src={logo} alt="Logo {school}" class="{className} object-contain" {...rest} />
{:else}
    <GraduationCap class={className} {...rest} />
{/if}
