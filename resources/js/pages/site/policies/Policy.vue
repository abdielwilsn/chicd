<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Info } from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/site/PageHeader.vue';
import { contact } from '@/routes';
import policies from '@/routes/policies';

type PolicyKey = 'privacy' | 'safeguarding' | 'terms';

type Props = {
    policy: PolicyKey;
};

const props = defineProps<Props>();

type PolicyContent = {
    title: string;
    href: string;
    intro: string;
    sections: { heading: string; paragraphs: string[] }[];
};

/**
 * Plain-language summaries of CHICD's commitments. The full approved policy
 * documents are published on the Policies & Accountability page.
 */
const content: Record<PolicyKey, PolicyContent> = {
    privacy: {
        title: 'Privacy Policy',
        href: policies.privacy.url(),
        intro: 'CHICD respects your privacy. This summary explains how we handle the personal information you share with us through this website.',
        sections: [
            {
                heading: 'Information we collect',
                paragraphs: [
                    'When you contact us, apply to volunteer, make a donation pledge, send a confidential enquiry or subscribe to updates, we collect the details you choose to provide, such as your name, contact details and message.',
                ],
            },
            {
                heading: 'How we use it',
                paragraphs: [
                    'We use your information only to respond to you, to manage your volunteering, partnership or donation, and, if you subscribe, to send you CHICD updates. We do not sell your personal information.',
                ],
            },
            {
                heading: 'How we protect it',
                paragraphs: [
                    'Enquiry details are stored in encrypted form and are only available to the staff responsible for responding. Confidential enquiries, including Safe Housing enquiries, receive particular care.',
                ],
            },
            {
                heading: 'Your choices',
                paragraphs: [
                    'You may ask us to tell you what information we hold about you, to correct it or to delete it, and you may unsubscribe from updates at any time by contacting us.',
                ],
            },
        ],
    },
    safeguarding: {
        title: 'Safeguarding Policy',
        href: policies.safeguarding.url(),
        intro: 'CHICD is committed to the safety, dignity and well-being of every child, adult and community member we work with.',
        sections: [
            {
                heading: 'Our commitment',
                paragraphs: [
                    'Our approach places importance on respectful engagement, confidentiality, safeguarding and appropriate responses to individual and community needs.',
                    'Everyone who works with or on behalf of CHICD, including staff, volunteers, consultants and partners, is expected to uphold these standards.',
                ],
            },
            {
                heading: 'Children and young people',
                paragraphs: [
                    'Our education work, including Show Up Ready to Learn (SURL), is delivered with schools and communities in ways that protect children’s dignity, privacy and safety.',
                ],
            },
            {
                heading: 'Vulnerable adults',
                paragraphs: [
                    'The Safe Housing Programme is guided by respect for individual dignity, confidentiality, personal autonomy and responsible safeguarding. The residential facility’s location and residents’ personal information are not publicly disclosed.',
                ],
            },
            {
                heading: 'Raising a concern',
                paragraphs: [
                    'If you have a safeguarding concern about anyone connected with CHICD, please tell us through our Complaints & Feedback page. If someone is in immediate danger, call the national emergency number 112.',
                ],
            },
        ],
    },
    terms: {
        title: 'Terms of Use',
        href: policies.terms.url(),
        intro: 'These terms apply when you use the CHICD website.',
        sections: [
            {
                heading: 'Using this website',
                paragraphs: [
                    'This website provides information about CHICD and its work. Please use it lawfully and do not attempt to disrupt it or access areas that are not intended for the public.',
                ],
            },
            {
                heading: 'Content',
                paragraphs: [
                    'Unless otherwise stated, the content on this website belongs to CHICD. You may share it for non-commercial purposes with appropriate credit to CHICD.',
                ],
            },
            {
                heading: 'Accuracy',
                paragraphs: [
                    'We work to keep information accurate and up to date, but it is provided for general information and may change as our programmes develop.',
                ],
            },
        ],
    },
};

const policyContent = computed(() => content[props.policy]);
</script>

<template>
    <Head :title="policyContent.title" />

    <div>
        <PageHeader
            eyebrow="Policies & Accountability"
            :title="policyContent.title"
            :breadcrumbs="[
                {
                    title: 'Policies & Accountability',
                    href: policies.index.url(),
                },
                { title: policyContent.title, href: policyContent.href },
            ]"
        >
            <p>{{ policyContent.intro }}</p>
        </PageHeader>

        <section class="py-16 sm:py-20">
            <div class="site-container max-w-3xl">
                <div
                    class="flex gap-3 border-l-4 border-sun-400 bg-cream p-5 text-stone-700"
                >
                    <Info class="mt-0.5 size-5 shrink-0 text-brand-600" />
                    <p>
                        This page is a summary. CHICD's full approved policy
                        will be available to download on the
                        <Link
                            :href="policies.index.url()"
                            class="font-semibold text-brand-700 underline"
                            >Policies &amp; Accountability</Link
                        >
                        page.
                    </p>
                </div>

                <div class="mt-12 space-y-10">
                    <section
                        v-for="section in policyContent.sections"
                        :key="section.heading"
                    >
                        <h2
                            class="font-heading text-[1.6rem] font-bold text-ink"
                        >
                            {{ section.heading }}
                        </h2>
                        <div class="site-prose mt-3">
                            <p
                                v-for="paragraph in section.paragraphs"
                                :key="paragraph"
                            >
                                {{ paragraph }}
                            </p>
                        </div>
                    </section>
                </div>

                <p class="mt-12 border-t border-stone-200 pt-6 text-stone-600">
                    Questions about this policy?
                    <Link
                        :href="contact()"
                        class="font-semibold text-brand-700 hover:underline"
                        >Contact us</Link
                    >.
                </p>
            </div>
        </section>
    </div>
</template>
