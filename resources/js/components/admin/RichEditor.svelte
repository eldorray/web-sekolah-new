<script lang="ts">
    import Bold from '@lucide/svelte/icons/bold';
    import Heading2 from '@lucide/svelte/icons/heading-2';
    import Heading3 from '@lucide/svelte/icons/heading-3';
    import Italic from '@lucide/svelte/icons/italic';
    import Link2 from '@lucide/svelte/icons/link-2';
    import List from '@lucide/svelte/icons/list';
    import ListOrdered from '@lucide/svelte/icons/list-ordered';
    import Quote from '@lucide/svelte/icons/quote';
    import RemoveFormatting from '@lucide/svelte/icons/remove-formatting';

    /**
     * ponytail: contenteditable + execCommand instead of a rich-text library.
     * Ceiling: no schema, so pasted markup is whatever the browser keeps —
     * the server sanitizes on save. Swap in Tiptap here if the editor ever
     * needs tables, images, or collaborative editing.
     */
    let {
        id,
        value = $bindable(''),
        placeholder = 'Tulis isi di sini',
    }: {
        id: string;
        value?: string;
        placeholder?: string;
    } = $props();

    let editor = $state<HTMLDivElement | null>(null);
    let hydrated = false;

    $effect(() => {
        if (editor && !hydrated) {
            editor.innerHTML = value;
            hydrated = true;
        }
    });

    function run(command: string, argument?: string): void {
        editor?.focus();
        document.execCommand(command, false, argument);
        sync();
    }

    function sync(): void {
        value = editor?.innerHTML ?? '';
    }

    function addLink(): void {
        const url = window.prompt('Alamat tautan (https://...)');

        if (url) {
            run('createLink', url);
        }
    }

    const tools = [
        { label: 'Tebal', icon: Bold, run: () => run('bold') },
        { label: 'Miring', icon: Italic, run: () => run('italic') },
        { label: 'Judul 2', icon: Heading2, run: () => run('formatBlock', 'h2') },
        { label: 'Judul 3', icon: Heading3, run: () => run('formatBlock', 'h3') },
        { label: 'Daftar', icon: List, run: () => run('insertUnorderedList') },
        { label: 'Daftar bernomor', icon: ListOrdered, run: () => run('insertOrderedList') },
        { label: 'Kutipan', icon: Quote, run: () => run('formatBlock', 'blockquote') },
        { label: 'Tautan', icon: Link2, run: addLink },
        { label: 'Bersihkan format', icon: RemoveFormatting, run: () => run('removeFormat') },
    ];
</script>

<div class="rounded-md border border-input">
    <div class="flex flex-wrap gap-0.5 border-b p-1.5" role="toolbar" aria-label="Format teks">
        {#each tools as tool (tool.label)}
            <button
                type="button"
                onclick={tool.run}
                title={tool.label}
                class="grid size-8 place-items-center rounded transition hover:bg-muted"
            >
                <span class="sr-only">{tool.label}</span>
                <tool.icon class="size-4" />
            </button>
        {/each}
    </div>

    <div
        {id}
        bind:this={editor}
        contenteditable="true"
        role="textbox"
        aria-multiline="true"
        tabindex="0"
        data-placeholder={placeholder}
        oninput={sync}
        onblur={sync}
        class="prose-editor min-h-64 max-w-none px-3 py-2 text-sm focus:outline-none"
    ></div>
</div>

<style>
    .prose-editor:empty::before {
        content: attr(data-placeholder);
        color: var(--color-muted-foreground);
    }

    .prose-editor :global(h2) {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0.75rem 0 0.35rem;
    }

    .prose-editor :global(h3) {
        font-size: 1.05rem;
        font-weight: 600;
        margin: 0.65rem 0 0.3rem;
    }

    .prose-editor :global(p) {
        margin: 0.4rem 0;
    }

    .prose-editor :global(ul),
    .prose-editor :global(ol) {
        margin: 0.4rem 0 0.4rem 1.25rem;
        list-style: revert;
    }

    .prose-editor :global(blockquote) {
        border-left: 3px solid var(--color-brand);
        margin: 0.5rem 0;
        padding-left: 0.75rem;
        color: var(--color-muted-foreground);
    }

    .prose-editor :global(a) {
        color: var(--color-brand);
        text-decoration: underline;
    }
</style>
