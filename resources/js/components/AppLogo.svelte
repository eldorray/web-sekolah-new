<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';

    const name = $derived(
        (page.props.school?.school_name as string | undefined) ??
            (page.props.name as string | undefined) ??
            'Sekolah',
    );
    const hasLogo = $derived(Boolean(page.props.branding?.logo));
</script>

<div
    class="flex aspect-square size-8 items-center justify-center rounded-md {hasLogo
        ? ''
        : 'bg-sidebar-primary text-sidebar-primary-foreground'}"
>
    <AppLogoIcon
        class={hasLogo ? 'size-8' : 'size-5 text-white dark:text-black'}
    />
</div>
<div class="ml-1 grid flex-1 text-left text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">{name}</span>
</div>
