<script lang="ts">
    import { reveal } from '@/lib/reveal';

    let {
        eyebrow,
        title,
        accent = '',
        text = '',
        align = 'center',
    }: {
        eyebrow: string;
        title: string;
        accent?: string;
        text?: string;
        align?: 'center' | 'left';
    } = $props();
</script>

<div
    use:reveal
    class={align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl'}
>
    <p class="eyebrow">
        <span class="h-px w-6 bg-sun"></span>{eyebrow}
    </p>
    <h2 class="section-title mt-3">
        {title}
        {#if accent}<span class="text-sun">{accent}</span>{/if}
    </h2>
    {#if text}
        <p class="mt-4 text-sm leading-relaxed text-ink-soft">{text}</p>
    {/if}
</div>
