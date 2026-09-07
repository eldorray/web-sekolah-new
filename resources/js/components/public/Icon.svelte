<script lang="ts">
    import BookMarked from '@lucide/svelte/icons/book-marked';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import Cpu from '@lucide/svelte/icons/cpu';
    import FlaskConical from '@lucide/svelte/icons/flask-conical';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import Languages from '@lucide/svelte/icons/languages';
    import LibraryBig from '@lucide/svelte/icons/library-big';
    import Microscope from '@lucide/svelte/icons/microscope';
    import Phone from '@lucide/svelte/icons/phone';
    import Sprout from '@lucide/svelte/icons/sprout';
    import Trophy from '@lucide/svelte/icons/trophy';
    import Users from '@lucide/svelte/icons/users';
    import Volleyball from '@lucide/svelte/icons/volleyball';

    const registry = {
        'book-marked': BookMarked,
        'book-open': BookOpen,
        'calendar-days': CalendarDays,
        cpu: Cpu,
        'flask-conical': FlaskConical,
        'graduation-cap': GraduationCap,
        languages: Languages,
        'library-big': LibraryBig,
        microscope: Microscope,
        phone: Phone,
        sprout: Sprout,
        trophy: Trophy,
        users: Users,
        volleyball: Volleyball,
    } as const;

    let {
        name,
        class: className = 'size-6',
    }: { name: string; class?: string } = $props();

    const Glyph = $derived(
        registry[name as keyof typeof registry] ?? BookOpen,
    );
</script>

<Glyph class={className} strokeWidth={1.75} aria-hidden="true" />
