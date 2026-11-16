<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LinkCard from '@/components/site/LinkCard.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PublicationLibrary from '@/components/site/PublicationLibrary.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import policies from '@/routes/policies';
import type { Publication, PublicationGroup } from '@/types';

type Props = {
    featuredAnnualReport: Publication | null;
    groups: PublicationGroup[];
};

defineProps<Props>();

const policyPages = [
    {
        title: 'Privacy Policy',
        description: 'How we collect, use and protect personal information.',
        href: policies.privacy.url(),
        accent: 'border-brand-600',
    },
    {
        title: 'Safeguarding Policy',
        description:
            'Our commitment to the safety and dignity of everyone we work with.',
        href: policies.safeguarding.url(),
        accent: 'border-sun-400',
    },
    {
        title: 'Complaints & Feedback',
        description: 'Raise a concern, make a complaint or share feedback.',
        href: policies.feedback.url(),
        accent: 'border-terracotta-600',
    },
    {
        title: 'Terms of Use',
        description: 'The terms that apply when you use this website.',
        href: policies.terms.url(),
        accent: 'border-lagoon-600',
    },
];
</script>

<template>
    <Head title="Policies & Accountability" />

    <div>
        <PageHeader
            eyebrow="Policies & Accountability"
            title="Accountable to the communities and partners who trust us"
            :breadcrumbs="[
                {
                    title: 'Policies & Accountability',
                    href: policies.index.url(),
                },
            ]"
        >
            <p>
                We believe responsible leadership involves sound
                decision-making, ethical conduct, effective stewardship of
                resources and accountability to the communities and partners who
                place their trust in our work.
            </p>
        </PageHeader>

        <section class="py-16 sm:py-20">
            <div class="site-container">
                <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="item in policyPages" :key="item.title">
                        <LinkCard
                            :href="item.href"
                            :title="item.title"
                            :description="item.description"
                            :accent="item.accent"
                            cta="Read more"
                        />
                    </li>
                </ul>
            </div>
        </section>

        <section class="pb-20 sm:pb-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Reports and governance documents"
                    title="Annual reports and policies"
                    class="mb-12"
                />
                <PublicationLibrary
                    :featured-annual-report="featuredAnnualReport"
                    :groups="groups"
                    empty-title="Governance documents will be published here"
                >
                    <template #empty>
                        <p>
                            Our annual reports, safeguarding information,
                            privacy and data protection notice and other
                            approved governance documents will be available to
                            download here.
                        </p>
                    </template>
                </PublicationLibrary>
            </div>
        </section>
    </div>
</template>
