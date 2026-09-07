<script lang="ts">
    import ImagePlus from '@lucide/svelte/icons/image-plus';
    import X from '@lucide/svelte/icons/x';

    let {
        id,
        name = '',
        label = 'Gambar',
        hint = 'JPG atau PNG, maksimal 2 MB.',
        current = '',
        required = false,
        onchange,
    }: {
        id: string;
        name?: string;
        label?: string;
        hint?: string;
        current?: string;
        required?: boolean;
        onchange?: (file: File | null) => void;
    } = $props();

    let chosen = $state<string | null>(null);
    let fileName = $state('');
    const preview = $derived(chosen ?? current);

    function pick(event: Event): void {
        const input = event.currentTarget as HTMLInputElement;
        const file = input.files?.[0] ?? null;

        fileName = file?.name ?? '';
        chosen = file ? URL.createObjectURL(file) : null;
        onchange?.(file);
    }

    function clear(): void {
        chosen = '';
        fileName = '';
        onchange?.(null);
    }
</script>

<div class="grid gap-2">
    <span class="text-sm font-medium">{label}</span>

    {#if preview}
        <div class="relative overflow-hidden rounded-lg border">
            <img src={preview} alt="" class="h-40 w-full object-cover" />
            <button
                type="button"
                onclick={clear}
                class="absolute top-2 right-2 grid size-8 place-items-center rounded-full bg-background/90 text-foreground shadow"
            >
                <span class="sr-only">Hapus gambar</span>
                <X class="size-4" />
            </button>
        </div>
    {/if}

    <label
        class="flex cursor-pointer flex-col items-center gap-2 rounded-lg border-2 border-dashed p-5 text-center hover:bg-muted/50"
    >
        <ImagePlus class="size-5 text-muted-foreground" />
        <span class="text-sm font-medium">
            {preview ? 'Ganti gambar' : 'Pilih gambar'}
        </span>
        <span class="text-xs text-muted-foreground">{fileName || hint}</span>
        <input
            {id}
            name={name || id}
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="sr-only"
            required={required && !preview}
            onchange={pick}
        />
    </label>
</div>
