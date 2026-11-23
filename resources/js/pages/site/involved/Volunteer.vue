<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BookOpen, Briefcase, Clock } from '@lucide/vue';
import EnquiryForm from '@/components/site/EnquiryForm.vue';
import FormPanel from '@/components/site/FormPanel.vue';
import OpportunityCard from '@/components/site/OpportunityCard.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import getInvolved from '@/routes/get-involved';
import type { Opportunity } from '@/types';

defineProps<{
    /** Open volunteer roles. */
    opportunities: Opportunity[];
}>();

const contributions = [
    {
        icon: Briefcase,
        title: 'Your skills',
        description:
            'Professional expertise in areas such as public health, research, education, communications or operations.',
    },
    {
        icon: BookOpen,
        title: 'Your knowledge',
        description:
            'Experience and insight that can strengthen programme design and community engagement.',
    },
    {
        icon: Clock,
        title: 'Your time',
        description:
            'Hands-on support for community activities, outreach and programme delivery.',
    },
];

const areasOfInterest = [
    'Show Up Ready to Learn (SURL)',
    'Safe Housing Programme',
    'Research and data',
    'Health education and outreach',
    'Communications and media',
    'Fundraising and events',
    'Wherever I am most useful',
];
</script>

<template>
    <Head title="Volunteer With Us" />

    <div>
        <PageHeader
            :image="siteImages.volunteer"
            eyebrow="Volunteering"
            title="Volunteer With Us"
            :breadcrumbs="[
                { title: 'Get Involved', href: getInvolved.index.url() },
                { title: 'Volunteering', href: getInvolved.volunteer.url() },
            ]"
        >
            <p>
                Contribute your skills, knowledge and time to programmes that
                support individuals and communities.
            </p>
            <template #actions>
                <SiteButton href="#apply" variant="donate" size="lg">
                    Apply to Volunteer
                </SiteButton>
            </template>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="How you can help"
                    title="Every contribution counts"
                >
                    <p>
                        As programmes develop, CHICD seeks to engage
                        professionals, volunteers and technical partners
                        according to the expertise required.
                    </p>
                </SectionIntro>
                <ul class="mt-12 grid gap-10 md:grid-cols-3">
                    <li
                        v-for="item in contributions"
                        :key="item.title"
                        class="border-t-2 border-brand-700 pt-6"
                    >
                        <component
                            :is="item.icon"
                            class="size-8 text-sun-600"
                            stroke-width="1.6"
                        />
                        <h3
                            class="mt-4 font-display text-[1.7rem] leading-none font-extrabold text-brand-900 uppercase"
                        >
                            {{ item.title }}
                        </h3>
                        <p class="mt-3 leading-relaxed text-stone-700">
                            {{ item.description }}
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <section
            v-if="opportunities.length"
            class="border-t border-stone-200 py-20 sm:py-24"
        >
            <div class="site-container">
                <div
                    class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between"
                >
                    <SectionIntro
                        eyebrow="Open roles"
                        title="Volunteer opportunities"
                    >
                        <p>
                            Roles we are recruiting volunteers for now. You can
                            also apply below to tell us where you would like to
                            help.
                        </p>
                    </SectionIntro>
                    <SiteButton
                        :href="getInvolved.opportunities.url()"
                        variant="outline"
                        class="self-start lg:self-auto"
                    >
                        All Opportunities
                    </SiteButton>
                </div>
                <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <li
                        v-for="opportunity in opportunities"
                        :key="opportunity.id"
                    >
                        <OpportunityCard :opportunity="opportunity" />
                    </li>
                </ul>
            </div>
        </section>

        <section id="apply" class="scroll-mt-28 bg-cream py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <SectionIntro title="Apply to volunteer">
                        <p>
                            Tell us a little about yourself, the skills you
                            would like to contribute and the kind of work that
                            interests you. A member of our team will be in
                            touch.
                        </p>
                    </SectionIntro>
                </div>
                <FormPanel class="lg:col-span-7">
                    <EnquiryForm
                        type="volunteer"
                        subject-label="Area of interest"
                        :subject-options="areasOfInterest"
                        message-label="Your skills, experience and availability"
                        submit-label="Submit application"
                    />
                </FormPanel>
            </div>
        </section>
    </div>
</template>
