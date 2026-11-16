<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CircleAlert,
    House,
    KeyRound,
    Lock,
    ShieldCheck,
    UserRound,
} from '@lucide/vue';
import CallToAction from '@/components/site/CallToAction.vue';
import EnquiryForm from '@/components/site/EnquiryForm.vue';
import FormPanel from '@/components/site/FormPanel.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PlusList from '@/components/site/PlusList.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import { donate } from '@/routes';
import getInvolved from '@/routes/get-involved';
import work from '@/routes/work';

const donateToSafeHousing = donate.url({ query: { fund: 'safe-housing' } });

const whoWeSupport = [
    'LGBTQ+ adults experiencing rejection, discrimination, threats or displacement.',
    'Survivors of domestic violence, abuse or exploitation who require temporary shelter.',
    'Adults experiencing homelessness, displacement or serious housing insecurity.',
    'People living with HIV or other stigmatised health conditions who experience housing-related exclusion.',
    'Persons with disabilities facing housing insecurity, where their needs can be safely accommodated.',
    'Other marginalised adults experiencing circumstances that make access to safe accommodation difficult.',
];

const whatWeProvide = [
    {
        icon: House,
        title: 'Safe Accommodation',
        description:
            'Temporary residential shelter within a structured environment.',
    },
    {
        icon: Lock,
        title: 'Privacy and Dignity',
        description:
            'Respectful accommodation arrangements and confidential handling of personal information.',
    },
    {
        icon: UserRound,
        title: 'Independent Living',
        description:
            'A residential setting that respects personal autonomy and individual responsibility.',
    },
    {
        icon: KeyRound,
        title: 'Basic Housing Support',
        description:
            'Accommodation and basic residential facilities during an approved stay.',
    },
];

const enquiryTips = [
    'You may use a first name or the name you prefer.',
    'Share only what you are comfortable sharing.',
    'Tell us the safest way to contact you.',
];
</script>

<template>
    <Head title="Safe Housing Programme" />

    <div>
        <PageHeader
            :image="siteImages.safeHousing"
            eyebrow="Safe Housing Programme · An Initiative of CHICD"
            title="A Safe Place. A Dignified Stay."
            :breadcrumbs="[
                { title: 'Our Work', href: work.index.url() },
                { title: 'Safe Housing', href: work.safeHousing.url() },
            ]"
        >
            <p>
                CHICD's Safe Housing Programme provides temporary accommodation
                for vulnerable and marginalised adults experiencing housing
                insecurity, social exclusion, discrimination or circumstances
                that place their safety and well-being at risk.
            </p>
            <p>
                The programme offers a respectful residential environment where
                eligible adults can access basic accommodation while maintaining
                their privacy, dignity and independence.
            </p>
            <template #actions>
                <SiteButton href="#enquiry" variant="light" size="lg">
                    Make a Confidential Enquiry
                </SiteButton>
                <SiteButton
                    :href="donateToSafeHousing"
                    variant="donate"
                    size="lg"
                >
                    Support Safe Housing
                </SiteButton>
            </template>
        </PageHeader>

        <div class="site-container mt-10">
            <div
                class="flex items-start gap-3 border-l-4 border-terracotta-600 bg-terracotta-50 px-5 py-4 text-terracotta-800"
            >
                <CircleAlert class="mt-0.5 size-5 shrink-0" />
                <p>
                    <strong>If you are in immediate danger,</strong> please call
                    the national emergency number
                    <a href="tel:112" class="font-bold underline">112</a> or go
                    to the nearest police station or hospital. This enquiry form
                    is not monitored around the clock.
                </p>
            </div>
        </div>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <SectionIntro title="Who We Support">
                        <p>
                            The programme is intended for adults aged 18 years
                            and above who need temporary accommodation because
                            of housing-related vulnerability.
                        </p>
                    </SectionIntro>
                    <p
                        class="mt-8 border-l-4 border-sun-400 pl-6 font-heading text-[1.15rem] leading-snug font-semibold text-brand-900 sm:text-[1.3rem]"
                    >
                        Eligibility is determined by individual circumstances,
                        not membership of a particular group alone.
                    </p>
                </div>
                <div class="lg:col-span-7">
                    <p
                        class="font-display text-lg font-bold tracking-[0.08em] text-brand-800 uppercase"
                    >
                        This may include
                    </p>
                    <ul
                        class="mt-4 divide-y divide-stone-200 border-y border-stone-200"
                    >
                        <li
                            v-for="item in whoWeSupport"
                            :key="item"
                            class="flex gap-4 py-4 leading-relaxed text-ink"
                        >
                            <ShieldCheck
                                class="mt-0.5 size-5 shrink-0 text-brand-600"
                            />
                            {{ item }}
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="bg-cream py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="What We Provide" />
                <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <li
                        v-for="item in whatWeProvide"
                        :key="item.title"
                        class="flex gap-4 border-t-[5px] border-brand-600 bg-white p-5 sm:block sm:p-7"
                    >
                        <component
                            :is="item.icon"
                            class="size-7 shrink-0 text-brand-700 sm:size-8"
                            stroke-width="1.6"
                        />
                        <div>
                            <h3
                                class="font-heading text-xl font-bold text-ink sm:mt-5"
                            >
                                {{ item.title }}
                            </h3>
                            <p class="mt-2 leading-relaxed text-stone-700">
                                {{ item.description }}
                            </p>
                        </div>
                    </li>
                </ul>

                <div class="mt-12 grid lg:grid-cols-2">
                    <div class="bg-brand-900 p-8 text-brand-100 sm:p-12">
                        <h2
                            class="font-heading text-[1.8rem] font-bold text-white"
                        >
                            Admission and Eligibility
                        </h2>
                        <div class="mt-4 space-y-3 leading-relaxed">
                            <p>
                                Applicants must be aged 18 years or above and
                                require temporary accommodation.
                            </p>
                            <p>
                                Admission is subject to an individual
                                eligibility and safety assessment, accommodation
                                availability and the programme's capacity to
                                meet the applicant's needs.
                            </p>
                        </div>
                    </div>
                    <div class="bg-white p-8 sm:p-12">
                        <h3
                            class="font-display text-xl font-bold tracking-[0.06em] text-brand-800 uppercase"
                        >
                            Please note
                        </h3>
                        <p class="mt-3 leading-relaxed text-stone-700">
                            Residents must be able to manage their personal care
                            and medication independently. The programme does not
                            provide clinical treatment, nursing care or
                            continuous personal assistance.
                        </p>
                        <SiteButton
                            href="#enquiry"
                            variant="outline"
                            class="mt-7"
                        >
                            Enquire About Eligibility
                        </SiteButton>
                    </div>
                </div>
            </div>
        </section>

        <section id="enquiry" class="scroll-mt-28 py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <SectionIntro
                        eyebrow="Confidential Enquiries"
                        title="Make a Confidential Enquiry"
                    >
                        <p>
                            All accommodation enquiries are handled with
                            attention to privacy, dignity and safety. The
                            residential facility's location is not publicly
                            disclosed.
                        </p>
                        <p>
                            Individuals, organisations and relevant service
                            providers may contact CHICD to enquire about the
                            programme, discuss eligibility or explore
                            appropriate forms of support and collaboration.
                        </p>
                    </SectionIntro>
                    <PlusList :items="enquiryTips" class="mt-8" />
                </div>
                <FormPanel class="lg:col-span-7">
                    <EnquiryForm
                        type="safe_housing"
                        :show-subject="false"
                        email-or-phone
                        confidential
                        name-label="Name"
                        name-hint="A first name or preferred name is fine."
                        message-label="How can we help?"
                        message-placeholder="Briefly describe your situation or question, and the safest times or ways to reach you."
                        submit-label="Send confidential enquiry"
                    />
                </FormPanel>
            </div>
        </section>

        <CallToAction
            eyebrow="Support Safe Housing"
            title="Help Keep a Safe Place Available"
        >
            <p>
                Your support helps CHICD maintain temporary accommodation and
                essential residential facilities for vulnerable and marginalised
                adults.
            </p>
            <template #actions>
                <SiteButton :href="donateToSafeHousing" variant="donate">
                    Donate to Safe Housing
                </SiteButton>
                <SiteButton
                    :href="getInvolved.partnerships.url()"
                    variant="light"
                >
                    Partner With Us
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
