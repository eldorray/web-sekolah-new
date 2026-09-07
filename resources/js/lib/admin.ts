/**
 * Static lookups for the admin panel: status labels and their reserved
 * colours, plus the icon names the Icon component can render.
 */

export type PpdbStatus = 'pending' | 'accepted' | 'rejected';

export const statusLabels: Record<PpdbStatus, string> = {
    pending: 'Menunggu',
    accepted: 'Diterima',
    rejected: 'Ditolak',
};

/**
 * PPDB status is a state, not a series: reserved status colors, always paired
 * with the label text so meaning never rests on color alone.
 */
export const statusBadgeClass: Record<PpdbStatus, string> = {
    pending:
        'border-transparent bg-amber-100 text-amber-900 dark:bg-amber-400/15 dark:text-amber-200',
    accepted:
        'border-transparent bg-emerald-100 text-emerald-900 dark:bg-emerald-400/15 dark:text-emerald-200',
    rejected:
        'border-transparent bg-red-100 text-red-900 dark:bg-red-400/15 dark:text-red-200',
};

export type VisitStatus = 'pending' | 'approved' | 'done' | 'rejected';

export const visitStatusLabels: Record<VisitStatus, string> = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    done: 'Selesai',
    rejected: 'Ditolak',
};

export const visitStatusClass: Record<VisitStatus, string> = {
    pending:
        'border-transparent bg-amber-100 text-amber-900 dark:bg-amber-400/15 dark:text-amber-200',
    approved:
        'border-transparent bg-emerald-100 text-emerald-900 dark:bg-emerald-400/15 dark:text-emerald-200',
    done: 'border-transparent bg-slate-100 text-slate-900 dark:bg-slate-400/15 dark:text-slate-200',
    rejected:
        'border-transparent bg-red-100 text-red-900 dark:bg-red-400/15 dark:text-red-200',
};

/** Mirrors the registry in components/public/Icon.svelte. */
export const iconLibrary = [
    'book-marked',
    'book-open',
    'calendar-days',
    'cpu',
    'flask-conical',
    'graduation-cap',
    'languages',
    'library-big',
    'microscope',
    'phone',
    'sprout',
    'trophy',
    'users',
    'volleyball',
];
