<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CallToAction from '@/components/site/CallToAction.vue';
import LinkCard from '@/components/site/LinkCard.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import getInvolved from '@/routes/get-involved';

const ways = [
    {
        title: 'Partner With CHICD',
        description:
            'Collaborate on health innovation, research, education, community development and sustainable livelihood initiatives.',
        href: getInvolved.partnerships.url(),
        cta: 'Explore Partnerships',
        image: siteImages.livelihoods,
        accent: 'border-brand-600',
    },
    {
        title: 'Volunteer With Us',
        description:
            'Contribute your skills, knowledge and time to programmes that support individuals and communities.',
        href: getInvolved.volunteer.url(),
        cta: 'Apply to Volunteer',
        image: siteImages.volunteer,
        accent: 'border-sun-400',
    },
    {
        title: 'Support a Programme',
        description:
            'Contribute to initiatives such as Show Up Ready to Learn (SURL) and the Safe Housing Programme.',
        href: getInvolved.support.url(),
        cta: 'Support Our Programmes',
        image: siteImages.surl,
        accent: 'border-terracotta-600',
    },
    {
        title: 'Opportunities',
        description:
            'Volunteer roles, research opportunities, internships, vacancies and consultancies currently open at CHICD.',
        href: getInvolved.opportunities.url(),
        cta: 'View Opportunities',
        image: siteImages.education,
        accent: 'border-brand-600',
    },
    {
        title: 'Research & Academic Collaboration',
        description:
            'Work with CHICD on community-based research, behavioural science, health innovation and evidence-informed interventions.',
        href: `${getInvolved.partnerships.url()}#research`,
        cta: 'Collaborate With Us',
        image: siteImages.research,
        accent: 'border-lagoon-600',
    },
    {
        title: 'Corporate & Institutional Partnerships',
        description:
            'Support community initiatives through corporate social responsibility, technical assistance, sponsorship and institutional collaboration.',
        href: `${getInvolved.partnerships.url()}#corporate`,
        cta: 'Become a Partner',
        image: siteImages.hero,
        accent: 'border-olive-600',
    },
];
</script>

<template>
    <Head title="Get Involved" />

    <div>
        <PageHeader
            :image="siteImages.training"
            eyebrow="Get Involved"
            title="Together, We Can Create Lasting Change"
            :breadcrumbs="[
                { title: 'Get Involved', href: getInvolved.index.url() },
            ]"
        >
            <p>
                Meaningful community development requires people, organisations
                and institutions working together.
            </p>
            <p>
                At CHICD, we welcome individuals, businesses, researchers and
                development partners who share our commitment to improving
                health, expanding opportunities and strengthening communities.
            </p>
            <template #actions>
                <SiteButton
                    :href="getInvolved.partnerships.url()"
                    variant="donate"
                    size="lg"
                >
                    Partner With Us
                </SiteButton>
                <SiteButton
                    :href="getInvolved.volunteer.url()"
                    variant="ghostLight"
                    size="lg"
                >
                    Become a Volunteer
                </SiteButton>
            </template>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="Ways to Get Involved" />
                <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-6">
                    <li
                        v-for="way in ways"
                        :key="way.title"
                        class="lg:col-span-2"
                    >
                        <LinkCard
                            :href="way.href"
                            :title="way.title"
                            :description="way.description"
                            :image="way.image"
                            :cta="way.cta"
                            :accent="way.accent"
                        />
                    </li>
                </ul>
            </div>
        </section>

        <CallToAction
            tone="sun"
            eyebrow="Let's Work Together"
            title="Have an idea, project or partnership opportunity?"
        >
            <p>
                We would love to explore how we can work together to create
                meaningful community impact.
            </p>
            <template #actions>
                <SiteButton :href="`${getInvolved.partnerships.url()}#enquiry`">
                    Contact Our Partnerships Team
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
