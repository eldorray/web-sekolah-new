<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { reveal } from '@/lib/reveal';
    import PageHero from '@/components/public/PageHero.svelte';
    import SectionHead from '@/components/public/SectionHead.svelte';
    import SocialIcon from '@/components/public/SocialIcon.svelte';
    type Teacher = {
        name: string;
        position: string | null;
        bio: string | null;
        photo: string;
        instagram: string | null;
        facebook: string | null;
    };

    let { teachers = [] }: { teachers?: Teacher[] } = $props();
</script>

<AppHead title="Tim Guru" />
<PageHero
    title="Tim Guru"
    crumb="Tim Guru"
    image="https://picsum.photos/seed/dh-teacher-hero/1600/500"
/>

<section class="bg-white py-20">
    <div class="shell">
        <SectionHead
            eyebrow="Tenaga pendidik"
            title="Orang-orang yang mendampingi"
            accent="setiap hari"
            text="{teachers.length} guru dan tenaga kependidikan yang mendampingi siswa setiap hari."
        />
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {#each teachers as teacher, teacherIndex (teacher.name)}
                <article
                    use:reveal={{ delay: (teacherIndex % 4) * 80 }}
                    class="group overflow-hidden rounded-2xl bg-white shadow-lg shadow-ink/5"
                >
                    <div class="relative aspect-4/5 overflow-hidden">
                        <img
                            src={teacher.photo}
                            alt={teacher.name}
                            loading="lazy"
                            class="size-full object-cover transition duration-500 group-hover:scale-105"
                        />
                        <div class="absolute inset-x-0 bottom-0 flex justify-center gap-2 bg-brand-deep/80 py-3 opacity-0 transition group-hover:opacity-100">
                            <a
                                href={teacher.instagram ?? 'https://instagram.com'}
                                target="_blank"
                                rel="noreferrer"
                                aria-label="Instagram {teacher.name}"
                                class="grid size-8 place-items-center rounded-full bg-white/15 text-white hover:bg-sun"
                            >
                                <SocialIcon name="instagram" />
                            </a>
                            <a
                                href={teacher.facebook ?? 'https://facebook.com'}
                                target="_blank"
                                rel="noreferrer"
                                aria-label="Facebook {teacher.name}"
                                class="grid size-8 place-items-center rounded-full bg-white/15 text-white hover:bg-sun"
                            >
                                <SocialIcon name="facebook" />
                            </a>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-display text-base leading-snug font-bold text-ink">
                            {teacher.name}
                        </h3>
                        <p class="mt-1 font-display text-xs font-bold text-sun uppercase">
                            {teacher.position}
                        </p>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                            {teacher.bio}
                        </p>
                    </div>
                </article>
            {/each}
        </div>
    </div>
</section>
