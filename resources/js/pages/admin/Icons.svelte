<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Pustaka Ikon', href: '/admin/icons' },
        ],
    };
</script>

<script lang="ts">
    import Check from '@lucide/svelte/icons/check';
    import Copy from '@lucide/svelte/icons/copy';
    import Search from '@lucide/svelte/icons/search';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import Icon from '@/components/public/Icon.svelte';
    import { Card } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    let { icons = [] }: { icons?: string[] } = $props();

    let search = $state('');
    let copied = $state('');

    const visible = $derived(
        icons.filter((name) =>
            name.includes(search.trim().toLowerCase()),
        ),
    );

    async function copy(name: string): Promise<void> {
        try {
            await navigator.clipboard.writeText(name);
            copied = name;
            toast.success(`Nama ikon "${name}" disalin.`);
        } catch {
            toast.error('Browser menolak akses papan klip. Salin manual dari kartu.');
        }
    }
</script>

<AppHead title="Pustaka Ikon" />

<div class="flex flex-col gap-6 p-4">
    <Heading
        title="Pustaka Ikon"
        description="Nama ikon yang bisa dipakai pada program dan menu situs."
    />

    <Card class="gap-4 py-5">
        <div class="px-5">
            <div class="relative max-w-sm">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    bind:value={search}
                    class="pl-9"
                    placeholder="Cari nama ikon"
                    aria-label="Cari ikon"
                />
            </div>
        </div>

        {#if visible.length > 0}
            <div class="grid gap-3 px-5 sm:grid-cols-3 lg:grid-cols-5">
                {#each visible as name (name)}
                    <button
                        type="button"
                        onclick={() => copy(name)}
                        class="flex flex-col items-center gap-2 rounded-lg border p-4 transition hover:border-brand hover:bg-muted/50"
                    >
                        <Icon {name} class="size-6" />
                        <span class="text-xs text-muted-foreground">{name}</span>
                        <span class="flex items-center gap-1 text-[11px] text-muted-foreground/70">
                            {#if copied === name}
                                <Check class="size-3" /> tersalin
                            {:else}
                                <Copy class="size-3" /> salin nama
                            {/if}
                        </span>
                    </button>
                {/each}
            </div>
        {:else}
            <p class="px-5 py-8 text-center text-sm text-muted-foreground">
                Tidak ada ikon yang cocok dengan "{search}".
            </p>
        {/if}

        <p class="px-5 text-xs text-muted-foreground">
            {visible.length} dari {icons.length} ikon.
        </p>
    </Card>
</div>
