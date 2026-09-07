/** Shapes the public pages receive from Inertia props. */

export type ProgramCard = {
    slug: string;
    icon: string;
    title: string;
    excerpt: string | null;
    image: string | null;
    badge: string | null;
};

export type NewsItem = {
    slug: string;
    title: string;
    category: string;
    excerpt: string | null;
    date: string | null;
    author: string | null;
    image: string | null;
};

export type GalleryPhoto = {
    id: number;
    image?: string;
    thumbnail: string;
    caption: string | null;
};

export type Stat = {
    icon: string;
    value: string;
    label: string;
};
