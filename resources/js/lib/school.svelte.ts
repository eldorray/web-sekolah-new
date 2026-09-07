import { page } from '@inertiajs/svelte';

/**
 * School identity from the shared Inertia props, so public copy follows
 * whatever admin saved in Pengaturan instead of hardcoded names.
 */
export type SchoolState = {
    readonly name: string;
    readonly foundation: string;
    /** Name without the jenjang prefix: "SMP Darul Hikmah" → "Darul Hikmah". */
    readonly shortName: string;
    /** Replaces {school}, {foundation} and {short} in a copy template. */
    fill: (template: string) => string;
};

const JENJANG = /^(SMP|SMA|SMK|MTs|MA|MI|SD|TK|PAUD|KB)\s+/i;

export function schoolState(): SchoolState {
    const name = $derived(
        (page.props.school?.school_name as string) ?? 'Sekolah',
    );
    const foundation = $derived(
        (page.props.school?.foundation_name as string) ?? 'yayasan',
    );
    const shortName = $derived(name.replace(JENJANG, '').trim() || name);

    return {
        get name() {
            return name;
        },
        get foundation() {
            return foundation;
        },
        get shortName() {
            return shortName;
        },
        fill(template: string): string {
            return template
                .replaceAll('{school}', name)
                .replaceAll('{foundation}', foundation)
                .replaceAll('{short}', shortName);
        },
    };
}
