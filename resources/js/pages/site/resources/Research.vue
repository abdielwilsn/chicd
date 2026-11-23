<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BookOpenText } from '@lucide/vue';
import CallToAction from '@/components/site/CallToAction.vue';
import EmptyState from '@/components/site/EmptyState.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PlusList from '@/components/site/PlusList.vue';
import PublicationLibrary from '@/components/site/PublicationLibrary.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import getInvolved from '@/routes/get-involved';
import resources from '@/routes/resources';
import type { Publication } from '@/types';

defineProps<{
    /** The latest research outputs, briefs, evaluations and toolkits. */
    publications: Publication[];
}>();

const interests = [
    'Public health',
    'Behavioural science and mental well-being',
    'Education',
    'Social inclusion and community development',
    'Technology for health and development',
];

const methods = [
    'Needs assessments and surveys',
    'Data analysis, monitoring and evaluation',
    'Programme evaluations and evidence synthesis',
];
</script>

<template>
    <Head title="Research & Insights" />

    <div>
        <PageHeader
            :image="siteImages.research"
            eyebrow="Research & Insights"
            title="Turning Knowledge Into Practical Solutions"
            :breadcrumbs="[
                { title: 'Resources', href: resources.research.url() },
                {
                    title: 'Research & Insights',
                    href: resources.research.url(),
                },
            ]"
        >
            <p>
                Research helps us understand community challenges, identify
                appropriate responses and learn from the outcomes of our
                interventions.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-7">
                    <p
                        class="font-heading text-[1.25rem] leading-snug font-semibold text-brand-900 sm:text-[1.5rem] lg:text-[1.7rem]"
                    >
                        At CHICD, we are interested in research that goes beyond
                        describing problems to informing decisions and improving
                        practice.
                    </p>
                    <div class="site-prose mt-6">
                        <p>
                            Our research interests include public health,
                            behavioural science, mental well-being, education,
                            social inclusion, community development and the
                            application of technology to health and development
                            challenges.
                        </p>
                        <p>
                            We seek to work with researchers, academic
                            institutions, practitioners and development partners
                            to generate useful evidence, strengthen programme
                            design and contribute to informed decision-making.
                        </p>
                        <p>
                            Through research, innovation and knowledge sharing,
                            we aim to connect scientific understanding with the
                            everyday realities of the communities we serve.
                        </p>
                    </div>
                </div>
                <aside
                    class="space-y-10 border-t-[6px] border-lagoon-600 bg-cream p-8 sm:p-10 lg:col-span-5"
                >
                    <div>
                        <h2
                            class="font-display text-xl font-bold tracking-[0.06em] text-lagoon-700 uppercase"
                        >
                            Research interests
                        </h2>
                        <PlusList :items="interests" class="mt-4" />
                    </div>
                    <div>
                        <h2
                            class="font-display text-xl font-bold tracking-[0.06em] text-lagoon-700 uppercase"
                        >
                            How we generate evidence
                        </h2>
                        <PlusList :items="methods" class="mt-4" />
                    </div>
                </aside>
            </div>
        </section>

        <section class="border-t border-stone-200 py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Knowledge hub"
                    title="Research, briefs and evaluations"
                >
                    <p>
                        Research outputs, policy briefs, programme evaluations
                        and toolkits will be shared here as they are completed.
                    </p>
                </SectionIntro>
                <template v-if="publications.length">
                    <PublicationLibrary
                        :featured-annual-report="null"
                        :groups="[
                            {
                                category: 'latest',
                                label: 'Latest outputs',
                                items: publications,
                            },
                        ]"
                        class="mt-10"
                    />
                    <SiteButton
                        :href="resources.publications.url()"
                        variant="outline"
                        class="mt-10"
                    >
                        Browse All Publications
                    </SiteButton>
                </template>
                <EmptyState
                    v-else
                    :icon="BookOpenText"
                    title="Our first research outputs are in development"
                    class="mt-10"
                >
                    <p>
                        In the meantime, published documents are available in
                        our Publications &amp; Reports library.
                    </p>
                    <template #actions>
                        <SiteButton
                            :href="resources.publications.url()"
                            variant="link"
                        >
                            Publications &amp; Reports
                        </SiteButton>
                    </template>
                </EmptyState>
            </div>
        </section>

        <CallToAction
            eyebrow="Research & Academic Collaboration"
            title="Research with CHICD"
        >
            <p>
                Work with CHICD on community-based research, behavioural
                science, health innovation and evidence-informed interventions.
            </p>
            <template #actions>
                <SiteButton
                    :href="`${getInvolved.partnerships.url()}#enquiry`"
                    variant="donate"
                >
                    Collaborate With Us
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
