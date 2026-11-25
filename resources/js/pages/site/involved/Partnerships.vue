<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Building2, Handshake, Microscope } from '@lucide/vue';
import EnquiryForm from '@/components/site/EnquiryForm.vue';
import FormPanel from '@/components/site/FormPanel.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PlusList from '@/components/site/PlusList.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import getInvolved from '@/routes/get-involved';

const partnershipTypes = [
    {
        id: 'partner',
        icon: Handshake,
        title: 'Programme partnerships',
        description:
            'Collaborate on health innovation, research, education, community development and sustainable livelihood initiatives.',
        accent: 'border-brand-600',
    },
    {
        id: 'research',
        icon: Microscope,
        title: 'Research & Academic Collaboration',
        description:
            'Work with CHICD on community-based research, behavioural science, health innovation and evidence-informed interventions.',
        accent: 'border-navy-600',
    },
    {
        id: 'corporate',
        icon: Building2,
        title: 'Corporate & Institutional Partnerships',
        description:
            'Support community initiatives through corporate social responsibility, technical assistance, sponsorship and institutional collaboration.',
        accent: 'border-sun-400',
    },
];

const opportunities = [
    'Programme implementation',
    'Research',
    'Technical assistance',
    'Training',
    'Resource mobilisation',
    'Community development initiatives',
];

const subjectOptions = [
    'Programme partnership',
    'Research & academic collaboration',
    'Corporate & institutional partnership',
    'Technical assistance or training',
    'Something else',
];
</script>

<template>
    <Head title="Partnerships" />

    <div>
        <PageHeader
            :image="siteImages.livelihoods"
            eyebrow="Partnerships"
            title="Partnerships That Support Practical Solutions"
            :breadcrumbs="[
                { title: 'Get Involved', href: getInvolved.index.url() },
                { title: 'Partnerships', href: getInvolved.partnerships.url() },
            ]"
        >
            <p>
                Addressing health and development challenges requires the
                participation of communities, professionals, institutions and
                organisations with different forms of expertise and resources.
            </p>
            <template #actions>
                <SiteButton href="#enquiry" variant="donate" size="lg">
                    Contact Our Partnerships Team
                </SiteButton>
            </template>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-7">
                    <SectionIntro
                        eyebrow="Work With Us"
                        title="Collaborating for meaningful outcomes"
                    >
                        <p>
                            CHICD welcomes collaboration with government
                            agencies, healthcare institutions, academic institutions,
                            research organisations, individuals, civil society groups,
                            businesses, foundations and development partners.
                        </p>
                        <p>
                            We are interested in partnerships that respond to
                            genuine needs, promote responsible practice and
                            contribute to meaningful improvements in people's
                            lives.
                        </p>
                    </SectionIntro>
                </div>
                <aside
                    class="self-start border-t-[6px] border-sun-400 bg-cream p-8 lg:col-span-5"
                >
                    <h3
                        class="font-display text-xl font-bold tracking-[0.06em] text-brand-800 uppercase"
                    >
                        Opportunities for collaboration may include
                    </h3>
                    <PlusList :items="opportunities" class="mt-5" />
                </aside>
            </div>
        </section>

        <section class="bg-cream py-20 sm:py-24">
            <div class="site-container grid gap-6 lg:grid-cols-3">
                <article
                    v-for="type in partnershipTypes"
                    :id="type.id"
                    :key="type.id"
                    class="flex scroll-mt-32 gap-4 border-t-[5px] bg-white p-5 sm:block sm:p-8"
                    :class="type.accent"
                >
                    <component
                        :is="type.icon"
                        class="size-7 shrink-0 text-brand-700 sm:size-8"
                        stroke-width="1.6"
                    />
                    <div>
                        <h2
                            class="font-heading text-xl leading-snug font-bold text-ink sm:mt-5"
                        >
                            {{ type.title }}
                        </h2>
                        <p class="mt-2 leading-relaxed text-stone-700">
                            {{ type.description }}
                        </p>
                    </div>
                </article>
            </div>
        </section>

        <section id="enquiry" class="scroll-mt-28 py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <SectionIntro
                        eyebrow="Let's Work Together"
                        title="Contact our partnerships team"
                    >
                        <p>
                            Have an idea, project or partnership opportunity? We
                            would love to explore how we can work together to
                            create meaningful community impact.
                        </p>
                    </SectionIntro>
                </div>
                <FormPanel class="lg:col-span-7">
                    <EnquiryForm
                        type="partnership"
                        show-organisation
                        subject-label="Type of partnership"
                        :subject-options="subjectOptions"
                        message-label="Tell us about your idea or organisation"
                        submit-label="Send partnership enquiry"
                    />
                </FormPanel>
            </div>
        </section>
    </div>
</template>
