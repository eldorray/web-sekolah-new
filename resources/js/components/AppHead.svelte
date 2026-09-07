<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';

    let {
        title = '',
        children,
    }: {
        title?: string;
        children?: Snippet;
    } = $props();

    /**
     * The suffix follows the school name saved in Pengaturan, so renaming the
     * school renames every browser tab. VITE_APP_NAME is only a build-time
     * fallback for the first paint.
     */
    const suffix = $derived(
        (page.props.school?.school_name as string | undefined) ||
            import.meta.env.VITE_APP_NAME ||
            '',
    );

    const fullTitle = $derived(
        [title, suffix].filter((part) => part !== '').join(' · ') || 'Sekolah',
    );
</script>

<svelte:head>
    <title>{fullTitle}</title>
    {@render children?.()}
</svelte:head>
