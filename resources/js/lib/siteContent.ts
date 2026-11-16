import type { LucideIcon } from '@lucide/vue';
import {
    Backpack,
    BookOpen,
    GraduationCap,
    HandHeart,
    HeartPulse,
    House,
    Laptop,
    Microscope,
    Shirt,
    Sprout,
    Venus,
    School,
} from '@lucide/vue';
import { contact, donate, home } from '@/routes';
import about from '@/routes/about';
import getInvolved from '@/routes/get-involved';
import policies from '@/routes/policies';
import resources from '@/routes/resources';
import work from '@/routes/work';

export type SiteImage = {
    src: string;
    alt: string;
};

/**
 * Photographs used across the website.
 *
 * These are TEMPORARY stock photos (Unsplash licence) used until CHICD's own
 * photographs are ready. To replace one, overwrite the file of the same name
 * in public/images/placeholders/ (or change its src) and update the alt text.
 * Unsplash photo IDs are noted so the source can be traced.
 */
export const siteImages = {
    /** photo-1687422809654-579d81c29d32 (cropped to remove phone numbers chalked on a freezer) */
    hero: {
        src: '/images/placeholders/hero.jpg',
        alt: 'A smiling trader holding a mobile phone at her market stall',
    },
    /** photo-1761666519794-ad6fbcef058b */
    community: {
        src: '/images/placeholders/community.jpg',
        alt: 'Older community members seated together at a gathering',
    },
    /** photo-1783408355447-b01a88767e6a */
    story: {
        src: '/images/placeholders/story.jpg',
        alt: 'Two health workers in scrubs walking along a community street',
    },
    /** photo-1735792339621-2846f164b78f */
    health: {
        src: '/images/placeholders/health.jpg',
        alt: 'A smiling mother sitting beside her young child',
    },
    /** photo-1576089275954-40cd98bfcfdb */
    research: {
        src: '/images/placeholders/research.jpg',
        alt: 'Two researchers working together in a laboratory',
    },
    /** photo-1665586510498-4d2e5d29e2f4 */
    education: {
        src: '/images/placeholders/education.jpg',
        alt: 'Students in uniform working at their desks in a classroom',
    },
    /** photo-1509100194014-d49809396daa */
    agriculture: {
        src: '/images/placeholders/agriculture.jpg',
        alt: 'A smiling woman tending crops on a farm',
    },
    /** photo-1617056239820-8ce90ba48193 */
    surl: {
        src: '/images/placeholders/surl.jpg',
        alt: 'Smiling schoolchildren in green and yellow uniforms',
    },
    /** photo-1632215861513-130b66fe97f4 */
    surlReading: {
        src: '/images/placeholders/surl-reading.jpg',
        alt: 'A teacher reading with pupils in a classroom',
    },
    /** photo-1655720359248-eeace8c709c5 */
    safeHousing: {
        src: '/images/placeholders/safe-housing.jpg',
        alt: 'Four hands holding one another in support',
    },
    /** photo-1560220604-1985ebfe28b1 */
    volunteer: {
        src: '/images/placeholders/volunteer.jpg',
        alt: 'Volunteers in matching yellow shirts at a community event',
    },
    /** photo-1665586510291-ae722d1d1f00 */
    training: {
        src: '/images/placeholders/training.jpg',
        alt: 'Participants seated at desks during a training session',
    },
    /** photo-1734255026082-82fdc81991f0 */
    livelihoods: {
        src: '/images/placeholders/livelihoods.jpg',
        alt: 'Traders at a market stall stacked with fresh tomatoes',
    },
} satisfies Record<string, SiteImage>;

export type SiteNavLink = {
    title: string;
    href: string;
    description?: string;
    /** Preview photo shown beside the link in the desktop dropdown. */
    image?: SiteImage;
};

export type SiteNavGroup = SiteNavLink & {
    children?: SiteNavLink[];
};

/**
 * Main navigation. Donate is rendered separately as a prominent button.
 */
export const mainNavigation: SiteNavGroup[] = [
    { title: 'Home', href: home.url() },
    {
        title: 'About Us',
        href: about.index.url(),
        children: [
            {
                title: 'Who We Are',
                image: siteImages.hero,
                href: about.index.url(),
                description:
                    'Our purpose, approach and the people we work with.',
            },
            {
                title: 'Our Story',
                image: siteImages.story,
                href: about.story.url(),
                description:
                    'From community service to an organised nonprofit.',
            },
            {
                title: 'Vision, Mission & Values',
                image: siteImages.health,
                href: about.values.url(),
                description: 'What we aim for and the values that guide us.',
            },
            {
                title: 'Leadership & Team',
                image: siteImages.community,
                href: about.leadership.url(),
                description: 'How CHICD is led and organised.',
            },
        ],
    },
    {
        title: 'Our Work',
        href: work.index.url(),
        children: [
            {
                title: 'Thematic Areas',
                image: siteImages.education,
                href: work.index.url(),
                description:
                    'Five interconnected areas of health and development.',
            },
            {
                title: 'Show Up Ready to Learn (SURL)',
                image: siteImages.surl,
                href: work.surl.url(),
                description: 'Removing practical barriers to education.',
            },
            {
                title: 'Safe Housing Programme',
                image: siteImages.safeHousing,
                href: work.safeHousing.url(),
                description: 'Temporary accommodation for vulnerable adults.',
            },
        ],
    },
    {
        title: 'Resources',
        href: resources.research.url(),
        children: [
            {
                title: 'Research & Insights',
                image: siteImages.research,
                href: resources.research.url(),
                description: 'Turning knowledge into practical solutions.',
            },
            {
                title: 'Publications & Reports',
                image: siteImages.surlReading,
                href: resources.publications.url(),
                description: 'Annual reports, research and other documents.',
            },
            {
                title: 'News & Stories',
                image: siteImages.livelihoods,
                href: resources.news.url(),
                description: 'Updates and perspectives from our work.',
            },
        ],
    },
    {
        title: 'Get Involved',
        href: getInvolved.index.url(),
        children: [
            {
                title: 'Partnerships',
                image: siteImages.training,
                href: getInvolved.partnerships.url(),
                description: 'Collaborate with CHICD on shared objectives.',
            },
            {
                title: 'Volunteering',
                image: siteImages.volunteer,
                href: getInvolved.volunteer.url(),
                description: 'Contribute your skills, knowledge and time.',
            },
            {
                title: 'Support Our Programmes',
                image: siteImages.agriculture,
                href: getInvolved.support.url(),
                description: 'Help sustain SURL and Safe Housing.',
            },
        ],
    },
    { title: 'Contact Us', href: contact.url() },
];

export const donateLink: SiteNavLink = { title: 'Donate', href: donate.url() };

export const footerQuickLinks: SiteNavLink[] = [
    { title: 'Home', href: home.url() },
    { title: 'About Us', href: about.index.url() },
    { title: 'Our Work', href: work.index.url() },
    { title: 'Programmes', href: `${work.index.url()}#programmes` },
    { title: 'Research & Insights', href: resources.research.url() },
    { title: 'Get Involved', href: getInvolved.index.url() },
    { title: 'Donate', href: donate.url() },
    { title: 'Contact', href: contact.url() },
];

export const footerPolicyLinks: SiteNavLink[] = [
    { title: 'Policies & Accountability', href: policies.index.url() },
    { title: 'Privacy Policy', href: policies.privacy.url() },
    { title: 'Safeguarding Policy', href: policies.safeguarding.url() },
    { title: 'Complaints & Feedback', href: policies.feedback.url() },
    { title: 'Terms of Use', href: policies.terms.url() },
    { title: 'Reports', href: resources.publications.url() },
];

export type ThematicArea = {
    number: number;
    slug: string;
    title: string;
    summary: string;
    heading: string;
    paragraphs: string[];
    icon: LucideIcon;
    image: SiteImage;
    /** Static Tailwind classes so each area keeps a recognisable colour. */
    tone: {
        /** Solid block with readable text on top. */
        tile: string;
        /** Accent text on a light background. */
        text: string;
        /** Accent rule along the top of cards. */
        border: string;
    };
};

export const thematicAreas: ThematicArea[] = [
    {
        number: 1,
        slug: 'health-innovation',
        title: 'Health Innovation and Community Well-being',
        summary:
            'Developing practical health solutions through technology, health education, disease prevention, community outreach and improved access to healthcare.',
        heading:
            'Improving Access to Health and Supporting Healthier Communities',
        paragraphs: [
            'Good health is essential to individual well-being and community development. Yet many people experience difficulties accessing appropriate healthcare, reliable health information and services that respond to their circumstances.',
            'CHICD is interested in developing and supporting practical approaches that improve health awareness, access to care and the delivery of community health services. These may include health education, disease prevention, community outreach, digital health technologies and innovations that strengthen existing healthcare systems.',
            'We also recognise the importance of addressing the social and behavioural factors that influence health outcomes. Our interest extends to approaches that help individuals and communities make informed health decisions and access appropriate support.',
            'Through research, collaboration and community engagement, we seek to contribute to healthcare solutions that are accessible, responsive and appropriate to local needs.',
        ],
        icon: HeartPulse,
        image: siteImages.health,
        tone: {
            tile: 'bg-brand-700 text-white',
            text: 'text-brand-700',
            border: 'border-brand-600',
        },
    },
    {
        number: 2,
        slug: 'research-evidence',
        title: 'Research, Evidence and Behavioural Change',
        summary:
            'Using research, data and behavioural science to understand community challenges, inform interventions and support mental well-being and positive behavioural change.',
        heading: 'Generating Knowledge That Informs Action',
        paragraphs: [
            'Understanding a problem is an important part of developing an effective response. Research helps identify community needs, examine the factors contributing to health and development challenges, and assess whether interventions are achieving their intended objectives.',
            'CHICD promotes the use of research, data and behavioural science to inform programme development and improve decision-making.',
            'Our interests include public health research, psychological and behavioural studies, community needs assessments, programme evaluation, mental well-being and the application of behavioural insights to health and development.',
            'We are particularly interested in research that contributes to practical solutions. This includes understanding why people experience difficulties accessing services, the factors influencing behaviour and how programmes can be designed to respond more effectively.',
            'We seek to collaborate with researchers, universities, practitioners and development organisations to generate and share knowledge that supports better policies, services and community interventions.',
        ],
        icon: Microscope,
        image: siteImages.research,
        tone: {
            tile: 'bg-lagoon-700 text-white',
            text: 'text-lagoon-700',
            border: 'border-lagoon-600',
        },
    },
    {
        number: 3,
        slug: 'education-skills',
        title: 'Education, Skills and Economic Empowerment',
        summary:
            'Expanding opportunities through education, digital inclusion, vocational training, entrepreneurship and livelihood development.',
        heading:
            'Expanding Opportunities for Learning and Sustainable Livelihoods',
        paragraphs: [
            "Education and economic opportunity influence people's ability to improve their circumstances and participate meaningfully in society. Limited access to learning resources, relevant skills and income-generating opportunities can reinforce disadvantage, particularly among underserved populations.",
            'CHICD supports initiatives that address barriers to education, promote skills development and strengthen opportunities for economic participation.',
            'Our interests include educational support, digital literacy, vocational and professional skills, entrepreneurship, employability and livelihood development.',
            "We recognise that meaningful empowerment involves more than providing immediate assistance. It also requires strengthening people's knowledge, capabilities and access to opportunities that can support their long-term development.",
            'Through community programmes, training, partnerships and practical interventions, we seek to help individuals develop the resources and capabilities needed to pursue educational and economic opportunities.',
        ],
        icon: GraduationCap,
        image: siteImages.education,
        tone: {
            tile: 'bg-sun-400 text-brand-950',
            text: 'text-sun-700',
            border: 'border-sun-400',
        },
    },
    {
        number: 4,
        slug: 'social-inclusion',
        title: 'Social Inclusion and Community Development',
        summary:
            'Supporting vulnerable and underserved populations, strengthening community participation and promoting equitable access to opportunities and essential services.',
        heading: 'Promoting Dignity, Safety and Participation',
        paragraphs: [
            "Social exclusion, discrimination, poverty and limited access to essential services can increase vulnerability and restrict people's opportunities to live safely and participate in their communities.",
            'CHICD promotes inclusive approaches that recognise the dignity, rights and experiences of individuals, particularly those facing social or economic disadvantage.',
            'Our interests include support for vulnerable adults, disability inclusion, community participation, social protection, psychosocial well-being and initiatives that strengthen community resilience.',
            'We recognise that vulnerable individuals may require different forms of support depending on their circumstances. Some may need access to information or services, while others may require temporary accommodation, practical assistance or opportunities to rebuild their independence.',
            'Our approach places importance on respectful engagement, confidentiality, safeguarding and appropriate responses to individual and community needs.',
        ],
        icon: HandHeart,
        image: siteImages.community,
        tone: {
            tile: 'bg-terracotta-600 text-white',
            text: 'text-terracotta-700',
            border: 'border-terracotta-600',
        },
    },
    {
        number: 5,
        slug: 'agriculture-environment',
        title: 'Sustainable Agriculture and Environment',
        summary:
            'Supporting food security, rural livelihoods, environmental health, water, sanitation and sustainable community practices.',
        heading: 'Supporting Livelihoods and Healthier Living Environments',
        paragraphs: [
            'Agriculture, food security and environmental conditions are closely connected to health and community well-being, particularly in rural and underserved areas.',
            'CHICD is interested in approaches that strengthen agricultural livelihoods, improve food security and promote environmentally sustainable community practices.',
            'Our areas of interest include sustainable agriculture, rural development, environmental health, water, sanitation and hygiene, and community-based responses to environmental challenges.',
            'We recognise that improving livelihoods and living conditions requires solutions that reflect local resources, environmental realities and the needs of the communities involved.',
            'Through partnerships, research, capacity development and locally appropriate initiatives, we seek to support healthier environments and more sustainable opportunities for communities.',
        ],
        icon: Sprout,
        image: siteImages.agriculture,
        tone: {
            tile: 'bg-olive-600 text-white',
            text: 'text-olive-700',
            border: 'border-olive-600',
        },
    },
];

export type Programme = {
    slug: 'surl' | 'safe-housing';
    name: string;
    tagline: string;
    summary: string[];
    href: string;
    cta: string;
    icon: LucideIcon;
    image: SiteImage;
};

export const programmes: Programme[] = [
    {
        slug: 'surl',
        name: 'Show Up Ready to Learn (SURL)',
        tagline: 'Supporting Educational Participation and Dignity',
        summary: [
            "Show Up Ready to Learn (SURL) is CHICD's education initiative focused on addressing practical barriers that affect children's participation in school, particularly in underserved communities.",
            'Through interventions such as the Rural School Uniform Project, SURL seeks to help children access basic school necessities and participate in education with greater dignity and confidence.',
        ],
        href: work.surl.url(),
        cta: 'Explore SURL',
        icon: School,
        image: siteImages.surl,
    },
    {
        slug: 'safe-housing',
        name: 'Safe Housing Programme for Vulnerable Adults',
        tagline: 'Providing Safety, Shelter and Dignity',
        summary: [
            "CHICD's Safe Housing Programme provides temporary accommodation for vulnerable adults who need a safe and supportive place to stay.",
            'The programme offers a structured residential environment that prioritises safety, privacy, independence and personal dignity. It provides accommodation and basic housing support for eligible adults while respecting their autonomy and individual circumstances.',
            'Admission is restricted to adults aged 18 years and above and is subject to eligibility and safety assessments. The facility does not operate as a medical or residential treatment centre.',
        ],
        href: work.safeHousing.url(),
        cta: 'Learn About Safe Housing',
        icon: House,
        image: siteImages.safeHousing,
    },
];

export type SurlProject = {
    title: string;
    description: string;
    icon: LucideIcon;
};

export const surlProjects: SurlProject[] = [
    {
        title: 'Rural School Uniform Project',
        description:
            'Providing appropriate school uniforms for children in underserved rural communities, helping to address a basic need that can affect their school experience.',
        icon: Shirt,
    },
    {
        title: 'School Essentials Project',
        description:
            'Supporting children with school bags, exercise books, writing materials and other basic learning necessities.',
        icon: Backpack,
    },
    {
        title: 'Ready to Read',
        description:
            'Promoting reading through age-appropriate books, reading activities and access to learning resources.',
        icon: BookOpen,
    },
    {
        title: 'Digital Learning Access',
        description:
            'Helping underserved schools and learners access basic digital learning opportunities and relevant technology.',
        icon: Laptop,
    },
    {
        title: "Girls' Education and Dignity",
        description:
            "Addressing practical barriers affecting girls' educational participation through essential support, health education and community engagement.",
        icon: Venus,
    },
    {
        title: 'Back to School Support',
        description:
            'Supporting efforts to help children who have experienced interruptions in schooling reconnect with educational opportunities.',
        icon: GraduationCap,
    },
];

/**
 * Funds a donor can choose to support, used by the Donate page.
 */
export const donationFunds = [
    { value: 'general', label: 'Donate to CHICD (most needed)' },
    { value: 'surl', label: 'Show Up Ready to Learn (SURL)' },
    { value: 'safe-housing', label: 'Safe Housing Programme' },
] as const;

export type DonationFund = (typeof donationFunds)[number]['value'];

/** Link to the newsletter sign-up section. */
export const newsletterAnchor = `${resources.news.url()}#newsletter`;
