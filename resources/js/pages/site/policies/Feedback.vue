<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CircleAlert } from '@lucide/vue';
import EnquiryForm from '@/components/site/EnquiryForm.vue';
import FormPanel from '@/components/site/FormPanel.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import policies from '@/routes/policies';

const feedbackTypes = [
    'Feedback or suggestion',
    'Complaint',
    'Safeguarding concern',
    'Concern about misconduct or misuse of resources',
];
</script>

<template>
    <Head title="Complaints & Feedback" />

    <div>
        <PageHeader
            eyebrow="Policies & Accountability"
            title="Complaints & Feedback"
            :breadcrumbs="[
                {
                    title: 'Policies & Accountability',
                    href: policies.index.url(),
                },
                {
                    title: 'Complaints & Feedback',
                    href: policies.feedback.url(),
                },
            ]"
        >
            <p>
                Your feedback helps us learn and improve. We take every concern
                seriously, including concerns about safeguarding, conduct or the
                use of resources.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="space-y-8 lg:col-span-5">
                    <SectionIntro title="How we handle your message">
                        <p>
                            Messages sent through this form go directly to the
                            CHICD team responsible for accountability. Concerns
                            are handled confidentially and fairly, and we will
                            let you know how we intend to respond.
                        </p>
                        <p>
                            You may give a first name only, but providing a way
                            to contact you helps us follow up.
                        </p>
                    </SectionIntro>
                    <div
                        class="flex gap-3 border-l-4 border-terracotta-600 bg-terracotta-50 p-5 text-terracotta-800"
                    >
                        <CircleAlert class="mt-0.5 size-5 shrink-0" />
                        <p>
                            If someone is in immediate danger, call the national
                            emergency number
                            <a href="tel:112" class="font-bold underline">112</a
                            >.
                        </p>
                    </div>
                </div>
                <FormPanel class="lg:col-span-7">
                    <EnquiryForm
                        type="feedback"
                        confidential
                        subject-label="What is this about?"
                        :subject-options="feedbackTypes"
                        message-label="Your message"
                        submit-label="Send feedback"
                    />
                </FormPanel>
            </div>
        </section>
    </div>
</template>
