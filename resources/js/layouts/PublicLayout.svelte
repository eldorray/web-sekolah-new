<script lang="ts">
    import type { Snippet } from 'svelte';
    import Footer from '@/components/public/Footer.svelte';
    import Navbar from '@/components/public/Navbar.svelte';
    import TopBar from '@/components/public/TopBar.svelte';
    import { Toaster } from '@/components/ui/sonner';

    let { children }: { children?: Snippet } = $props();
</script>

<div class="flex min-h-screen flex-col bg-white font-display text-ink">
    <a
        href="#konten"
        class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-brand focus:px-4 focus:py-2 focus:text-white"
    >
        Lewati ke konten
    </a>
    <TopBar />
    <Navbar />
    <main id="konten" class="flex-1">
        {@render children?.()}
    </main>
    <Footer />
    <Toaster />
</div>
