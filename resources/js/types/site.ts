export type SiteProps = {
    name: string;
    shortName: string;
    tagline: string;
    contact: {
        email: string | null;
        phone: string | null;
        address: string | null;
    };
    social: Partial<Record<'facebook' | 'instagram' | 'linkedin', string>>;
};

export type Publication = {
    id: number;
    title: string;
    category: string;
    category_label: string;
    year: number;
    summary: string | null;
    file_url: string;
    file_size: string | null;
    cover_image_url: string | null;
};

export type PublicationGroup = {
    category: string;
    label: string;
    items: Publication[];
};
