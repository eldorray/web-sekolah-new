<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import CalendarCheck from '@lucide/svelte/icons/calendar-check';
    import FileText from '@lucide/svelte/icons/file-text';
    import FolderGit2 from '@lucide/svelte/icons/folder-git-2';
    import Globe from '@lucide/svelte/icons/globe';
    import Images from '@lucide/svelte/icons/images';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import Mail from '@lucide/svelte/icons/mail';
    import Newspaper from '@lucide/svelte/icons/newspaper';
    import PenLine from '@lucide/svelte/icons/pen-line';
    import Settings from '@lucide/svelte/icons/settings';
    import Shapes from '@lucide/svelte/icons/shapes';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import UserRound from '@lucide/svelte/icons/user-round';
    import MonitorPlay from '@lucide/svelte/icons/monitor-play';
    import Users from '@lucide/svelte/icons/users';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavGroup from '@/components/NavGroup.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const panelNavItems: NavItem[] = [
        { title: 'Dashboard', href: '/admin', icon: LayoutGrid },
        { title: 'PPDB', href: '/admin/ppdb', icon: UserPlus },
        { title: 'Pesan masuk', href: '/admin/contacts', icon: Mail },
        { title: 'Kunjungan', href: '/admin/visits', icon: CalendarCheck },
    ];

    const contentNavItems: NavItem[] = [
        { title: 'Berita', href: '/admin/news', icon: Newspaper },
        { title: 'Program', href: '/admin/programs', icon: Sparkles },
        { title: 'Guru', href: '/admin/teachers', icon: Users },
        { title: 'Brosur', href: '/admin/brochures', icon: FileText },
        { title: 'Galeri', href: '/admin/gallery', icon: Images },
        { title: 'Video', href: '/admin/videos', icon: MonitorPlay },
        { title: 'Pustaka Ikon', href: '/admin/icons', icon: Shapes },
    ];

    const systemNavItems: NavItem[] = [
        { title: 'Pengguna', href: '/admin/users', icon: Users },
        { title: 'Pengaturan', href: '/admin/settings', icon: Settings },
    ];

    const guruNavItems: NavItem[] = [
        { title: 'Dashboard guru', href: '/guru', icon: LayoutGrid },
        { title: 'Berita saya', href: '/guru/news', icon: PenLine },
        { title: 'Profil saya', href: '/guru/profile', icon: UserRound },
    ];

    const footerNavItems: NavItem[] = [
        { title: 'Lihat situs', href: '/', icon: Globe },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link {...props} href="/admin" class={props.class}>
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={panelNavItems} label="Panel" />
        <NavGroup items={contentNavItems} label="Konten" />
        <NavGroup items={systemNavItems} label="Sistem" />
        <NavGroup items={guruNavItems} label="Guru" />
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
