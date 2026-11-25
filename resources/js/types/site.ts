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
    /** Published added programmes, as links for the Our Work menu. */
    programmes: ProgrammeNavLink[];
};

export type ProgrammeNavLink = {
    title: string;
    href: string;
    description: string | null;
    image: { src: string; alt: string };
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

/**
 * A publication as sent by AdminPublicationResource.
 */
export type AdminPublication = Publication & {
    is_published: boolean;
    is_featured: boolean;
    published_on: string | null;
    updated_on: string | null;
};

export type Option = {
    value: string;
    label: string;
};

/**
 * A programme added by an admin, as sent by ProgrammeResource. Summary and
 * body are already split into paragraphs.
 */
export type AddedProgramme = {
    id: number;
    name: string;
    slug: string;
    tagline: string | null;
    summary: string[];
    body: string[];
    image: { src: string; alt: string };
    url: string;
};

export type AdminProgramme = AddedProgramme & {
    summary_text: string;
    body_text: string | null;
    image_alt: string | null;
    sort_order: number;
    is_published: boolean;
    published_on: string | null;
};

export type TeamMember = {
    id: number;
    name: string;
    position: string;
    section: string;
    bio: string | null;
    photo_url: string | null;
    initials: string;
};

export type TeamSectionGroup = {
    section: string;
    label: string;
    members: TeamMember[];
};

export type AdminTeamMember = TeamMember & {
    section_label: string;
    sort_order: number;
    is_published: boolean;
};

export type Opportunity = {
    id: number;
    title: string;
    slug: string;
    type: string;
    type_label: string;
    location: string | null;
    summary: string;
    body: string[];
    closes_on: string | null;
    is_closed: boolean;
    application_url: string | null;
    attachment_url: string | null;
    attachment_size: string | null;
    url: string;
};

export type AdminOpportunity = Opportunity & {
    body_text: string | null;
    closes_on_date: string | null;
    is_published: boolean;
    published_on: string | null;
};

/**
 * A story, news article, event or announcement, as sent by PostResource.
 * Dates are already formatted in the organisation timezone.
 */
export type Post = {
    id: number;
    title: string;
    slug: string;
    type: string;
    type_label: string;
    excerpt: string;
    body: string[];
    cover_image: { src: string; alt: string } | null;
    event_starts: string | null;
    event_day: string | null;
    event_month: string | null;
    event_location: string | null;
    published_on: string | null;
    url: string;
};

export type AdminPost = Post & {
    excerpt_text: string | null;
    body_text: string;
    cover_image_alt: string | null;
    event_starts_at: string | null;
    is_published: boolean;
};

export type MediaItem = {
    id: number;
    type: 'photo' | 'video';
    caption: string | null;
    alt: string;
    url: string;
    poster_url: string | null;
};

export type AdminMediaItem = MediaItem & {
    type_label: string;
    alt_text: string | null;
    file_size: string | null;
    show_in_hero: boolean;
    sort_order: number;
};

export type Pagination = {
    currentPage: number;
    lastPage: number;
    previousUrl: string | null;
    nextUrl: string | null;
};

export type DonationFrequencyValue = 'one_time' | 'monthly' | 'annually';

export type DonationStatusValue =
    | 'pending'
    | 'successful'
    | 'failed'
    | 'abandoned';

/** A donation as sent by AdminDonationResource. Amounts are formatted. */
export type AdminDonation = {
    id: number;
    reference: string;
    donor_name: string;
    donor_email: string;
    donor_phone: string | null;
    fund: string;
    frequency: string;
    is_recurring: boolean;
    is_renewal: boolean;
    amount: string;
    fees: string | null;
    channel: string | null;
    status: DonationStatusValue;
    status_label: string;
    paid_on: string | null;
    started_on: string | null;
};
