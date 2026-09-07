<script lang="ts">
    import type { Snippet } from 'svelte';

    export type Column = {
        label: string;
        align?: 'left' | 'right';
        class?: string;
    };

    let {
        columns,
        empty = 'Belum ada data.',
        isEmpty = false,
        minWidth = '44rem',
        rows,
    }: {
        columns: Column[];
        empty?: string;
        isEmpty?: boolean;
        minWidth?: string;
        rows: Snippet;
    } = $props();
</script>

<div class="overflow-x-auto px-5">
    <table class="w-full text-sm" style="min-width: {minWidth}">
        <thead>
            <tr class="border-b text-left text-xs text-muted-foreground">
                {#each columns as column (column.label)}
                    <th
                        scope="col"
                        class="py-2 font-medium {column.align === 'right'
                            ? 'text-right'
                            : ''} {column.class ?? ''}"
                    >
                        {column.label}
                    </th>
                {/each}
            </tr>
        </thead>
        <tbody>
            {#if isEmpty}
                <tr>
                    <td
                        colspan={columns.length}
                        class="py-10 text-center text-muted-foreground"
                    >
                        {empty}
                    </td>
                </tr>
            {:else}
                {@render rows()}
            {/if}
        </tbody>
    </table>
</div>
