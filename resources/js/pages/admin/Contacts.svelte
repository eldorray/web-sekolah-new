<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Panel Admin', href: '/admin' },
            { title: 'Pesan', href: '/admin/contacts' },
        ],
    };
</script>

<script lang="ts">
    import Mail from '@lucide/svelte/icons/mail';
    import MailOpen from '@lucide/svelte/icons/mail-open';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import ConfirmDelete from '@/components/admin/ConfirmDelete.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { router } from '@inertiajs/svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import ContactController from '@/actions/App/Http/Controllers/Admin/ContactController';

    type Message = {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        subject: string;
        message: string;
        created_at: string | null;
        is_read: boolean;
    };

    let { messages = [] }: { messages?: Message[] } = $props();

    let filter = $state<'all' | 'unread'>('all');
    let selectedId = $state<number | null>(null);
    let pendingDelete = $state<number | null>(null);

    const visible = $derived(
        filter === 'unread' ? messages.filter((row) => !row.is_read) : messages,
    );

    const selected = $derived(
        messages.find((row) => row.id === selectedId) ?? visible[0] ?? null,
    );

    const target = $derived(
        messages.find((row) => row.id === pendingDelete) ?? null,
    );
</script>

<AppHead title="Pesan masuk" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <Heading
            title="Pesan masuk"
            description="Pesan dari formulir kontak situs publik."
        />
        <div class="flex gap-1">
            <Button
                variant={filter === 'all' ? 'default' : 'secondary'}
                size="sm"
                onclick={() => (filter = 'all')}
            >
                Semua
            </Button>
            <Button
                variant={filter === 'unread' ? 'default' : 'secondary'}
                size="sm"
                onclick={() => (filter = 'unread')}
            >
                Belum dibaca
            </Button>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-5">
        <Card class="gap-0 py-0 lg:col-span-2">
            {#if visible.length > 0}
                <ul class="divide-y">
                    {#each visible as row (row.id)}
                        <li>
                            <button
                                type="button"
                                onclick={() => (selectedId = row.id)}
                                class="w-full px-4 py-3 text-left transition hover:bg-muted/50 {selectedId ===
                                row.id
                                    ? 'bg-muted/70'
                                    : ''}"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-medium">
                                        {row.subject}
                                    </span>
                                    {#if !row.is_read}
                                        <Badge
                                            variant="outline"
                                            class="border-transparent bg-brand/10 text-brand"
                                        >
                                            Baru
                                        </Badge>
                                    {/if}
                                </div>
                                <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                    {row.name} · {row.created_at}
                                </p>
                            </button>
                        </li>
                    {/each}
                </ul>
            {:else}
                <p class="p-8 text-center text-sm text-muted-foreground">
                    Semua pesan sudah dibaca.
                </p>
            {/if}
        </Card>

        {#if selected}
            <Card class="gap-4 py-5 lg:col-span-3">
                <div class="flex flex-wrap items-start justify-between gap-3 px-5">
                    <div>
                        <h3 class="text-base font-medium">{selected.subject}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {selected.name} · {selected.email} · {selected.phone}
                        </p>
                        <p class="text-xs text-muted-foreground">{selected.created_at}</p>
                    </div>
                    <div class="flex gap-1">
                        <Button
                            variant="secondary"
                            size="sm"
                            disabled={selected.is_read}
                            onclick={() =>
                                router.put(
                                    ContactController.markRead.url({
                                        contact: selected.id,
                                    }),
                                    {},
                                    { preserveScroll: true },
                                )}
                        >
                            {#if selected.is_read}
                                <MailOpen class="size-4" />
                                Sudah dibaca
                            {:else}
                                <Mail class="size-4" />
                                Tandai dibaca
                            {/if}
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            onclick={() => (pendingDelete = selected.id)}
                        >
                            <Trash2 class="size-4 text-destructive" />
                            <span class="sr-only">Hapus pesan</span>
                        </Button>
                    </div>
                </div>

                <p class="px-5 text-sm leading-relaxed">{selected.message}</p>

                <div class="px-5">
                    <Button variant="outline" asChild>
                        {#snippet children(props)}
                            <a
                                {...props}
                                href={`mailto:${selected.email}?subject=Re: ${selected.subject}`}
                                class={props.class}
                            >
                                Balas lewat email
                            </a>
                        {/snippet}
                    </Button>
                </div>
            </Card>
        {/if}
    </div>
</div>

<ConfirmDelete
    open={pendingDelete !== null}
    title="Hapus pesan ini?"
    description={`Pesan "${target?.subject}" dari ${target?.name} akan dihapus.`}
    confirmLabel="Hapus pesan"
    onOpenChange={(value) => {
        if (!value) {
            pendingDelete = null;
        }
    }}
    onConfirm={() => {
        if (target === null) {
            return;
        }

        router.delete(ContactController.destroy.url({ contact: target.id }), {
            preserveScroll: true,
            onFinish: () => (pendingDelete = null),
        });
    }}
/>
