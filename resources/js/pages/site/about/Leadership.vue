<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CallToAction from '@/components/site/CallToAction.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import about from '@/routes/about';
import getInvolved from '@/routes/get-involved';
import policies from '@/routes/policies';
import type { TeamSectionGroup } from '@/types';

defineProps<{
    /** The people listed on the page, grouped by section. */
    teamSections: TeamSectionGroup[];
}>();

const teams = [
    'Programme and partnership coordination',
    'Research',
    'Operations',
    'Communications',
];

const expertise = [
    'Public health',
    'Research',
    'Psychology',
    'Innovation',
    'Programme development',
    'Community engagement',
];
</script>

<template>
    <Head title="Leadership & Team" />

    <div>
        <PageHeader
            eyebrow="About CHICD"
            title="Leadership & Team"
            :breadcrumbs="[
                { title: 'About Us', href: about.index.url() },
                { title: 'Leadership & Team', href: about.leadership.url() },
            ]"
        >
            <p>
                CHICD is building a multidisciplinary organisation that brings
                together expertise in public health, research, psychology,
                innovation, programme development and community engagement.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="How we are organised"
                    title="Our organisational structure"
                >
                    <p>
                        Our organisational structure includes executive
                        leadership, programme and partnership coordination,
                        research, operations and communications.
                    </p>
                    <p>
                        As programmes develop, we seek to engage additional
                        professionals, volunteers, consultants and technical
                        partners according to the expertise required.
                    </p>
                </SectionIntro>

                <div
                    class="mt-14"
                    role="group"
                    aria-label="Organisational structure"
                >
                    <div
                        class="mx-auto max-w-md border-t-[6px] border-sun-400 bg-brand-900 px-6 py-5 text-center text-white"
                    >
                        <p
                            class="font-display text-2xl font-extrabold tracking-[0.04em] uppercase"
                        >
                            Executive leadership
                        </p>
                    </div>
                    <div
                        class="mx-auto hidden h-10 w-0.5 bg-brand-700 lg:block"
                        aria-hidden="true"
                    />
                    <ul
                        class="relative mt-6 grid gap-4 sm:grid-cols-2 lg:mt-0 lg:grid-cols-4 lg:gap-6 lg:pt-10 lg:before:absolute lg:before:inset-x-[12.5%] lg:before:top-0 lg:before:h-0.5 lg:before:bg-brand-700"
                    >
                        <li
                            v-for="team in teams"
                            :key="team"
                            class="relative border-t-[5px] border-brand-600 bg-cream px-6 py-5 lg:before:absolute lg:before:-top-10 lg:before:left-1/2 lg:before:h-10 lg:before:w-0.5 lg:before:bg-brand-700"
                        >
                            <p
                                class="font-display text-xl leading-tight font-bold text-brand-900 uppercase"
                            >
                                {{ team }}
                            </p>
                        </li>
                    </ul>
                </div>

                <p class="mt-12 text-stone-600">
                    <span
                        class="font-display font-bold tracking-[0.08em] text-brand-800 uppercase"
                        >Expertise we bring together:</span
                    >
                    {{ expertise.join(' · ') }}
                </p>
            </div>
        </section>

        <section
            v-for="group in teamSections"
            :key="group.section"
            class="border-t border-stone-200 py-20 sm:py-24"
            :aria-labelledby="`team-${group.section}`"
        >
            <div class="site-container">
                <p class="site-label mb-5">Meet the people</p>
                <h2 :id="`team-${group.section}`" class="site-heading">
                    {{ group.label }}
                </h2>
                <ul
                    class="mt-12 grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="member in group.members"
                        :key="member.id"
                        class="border-t-[5px] border-brand-600 pt-6"
                    >
                        <img
                            v-if="member.photo_url"
                            :src="member.photo_url"
                            :alt="`Photograph of ${member.name}`"
                            loading="lazy"
                            class="aspect-square w-full max-w-[16rem] object-cover"
                        />
                        <div
                            v-else
                            class="flex aspect-square w-full max-w-[16rem] items-center justify-center bg-cream font-display text-6xl font-extrabold text-brand-700"
                            aria-hidden="true"
                        >
                            {{ member.initials }}
                        </div>
                        <h3
                            class="mt-5 font-heading text-xl leading-snug font-bold text-ink"
                        >
                            {{ member.name }}
                        </h3>
                        <p
                            class="mt-1 font-display text-[1rem] font-bold tracking-[0.08em] text-sun-700 uppercase"
                        >
                            {{ member.position }}
                        </p>
                        <p
                            v-if="member.bio"
                            class="mt-3 leading-relaxed whitespace-pre-line text-stone-700"
                        >
                            {{ member.bio }}
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="bg-cream py-20 sm:py-24">
            <div class="site-container">
                <div class="max-w-4xl">
                    <p
                        class="font-heading text-[1.3rem] leading-snug font-semibold text-brand-900 sm:text-[1.6rem] lg:text-[2rem]"
                    >
                        We believe responsible leadership involves sound
                        decision-making, ethical conduct, effective stewardship
                        of resources and accountability to the communities and
                        partners who place their trust in our work.
                    </p>
                    <SiteButton
                        :href="policies.index.url()"
                        variant="outline"
                        class="mt-9"
                    >
                        View Our Policies and Reports
                    </SiteButton>
                </div>
            </div>
        </section>

        <CallToAction title="Bring your expertise to CHICD">
            <p>
                We welcome professionals, volunteers, consultants and technical
                partners who share our commitment to communities.
            </p>
            <template #actions>
                <SiteButton :href="getInvolved.volunteer.url()" variant="light">
                    Volunteer With Us
                </SiteButton>
                <SiteButton
                    :href="getInvolved.partnerships.url()"
                    variant="ghostLight"
                >
                    Partner With Us
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
