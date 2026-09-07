<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import { untrack } from 'svelte';
    import {
        SidebarGroup,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
        useSidebar,
    } from '@/components/ui/sidebar';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import type { NavItem } from '@/types';

    let {
        items = [],
        label,
    }: {
        items: NavItem[];
        label: string;
    } = $props();

    const url = currentUrlState();
    const sidebar = useSidebar();

    const hasActiveChild = $derived(
        items.some((item) => url.isCurrentOrParentUrl(item.href, url.currentUrl)),
    );

    let open = $state(untrack(() => hasActiveChild));

    // A page inside this group opens it, so the current item is never hidden.
    $effect(() => {
        if (hasActiveChild) {
            open = true;
        }
    });

    const sidebarState = sidebar.state;

    // In icon-only mode the labels are hidden, so the items always show.
    const expanded = $derived(open || $sidebarState === 'collapsed');
</script>

<SidebarGroup class="px-2 py-0">
    <button
        type="button"
        onclick={() => (open = !open)}
        aria-expanded={open}
        class="text-sidebar-foreground/70 ring-sidebar-ring hover:text-sidebar-foreground flex h-8 w-full shrink-0 items-center justify-between rounded-md px-2 text-xs font-medium transition-[margin,opacity] duration-200 ease-linear outline-hidden focus-visible:ring-2 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0"
    >
        {label}
        <ChevronDown
            class="size-3.5 transition-transform duration-200 {open
                ? 'rotate-180'
                : ''}"
        />
    </button>

    <div
        class="grid transition-[grid-template-rows] duration-200 ease-out {expanded
            ? 'grid-rows-[1fr]'
            : 'grid-rows-[0fr]'}"
    >
        <div class="overflow-hidden">
            <SidebarMenu>
                {#each items as item (toUrl(item.href))}
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            isActive={url.isCurrentUrl(item.href, url.currentUrl)}
                            tooltip={item.title}
                        >
                            {#snippet children(props)}
                                <Link
                                    {...props}
                                    href={toUrl(item.href)}
                                    class={props.class}
                                >
                                    {#if item.icon}
                                        <item.icon class="size-4 shrink-0" />
                                    {/if}
                                    <span>{item.title}</span>
                                </Link>
                            {/snippet}
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                {/each}
            </SidebarMenu>
        </div>
    </div>
</SidebarGroup>
