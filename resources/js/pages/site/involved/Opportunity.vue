<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CircleAlert, Clock, Download, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { contact } from '@/routes';
import getInvolved from '@/routes/get-involved';
import type { Opportunity } from '@/types';

const props = defineProps<{
    opportunity: Opportunity;
}>();

const page = usePage();

/** Without an application link, applicants email CHICD or use the contact form. */
const applyHref = computed(() => {
    if (props.opportunity.application_url) {
        return props.opportunity.application_url;
    }

    const email = page.props.site.contact.email;

    return email
        ? `mailto:${email}?subject=${encodeURIComponent(`Application: ${props.opportunity.title}`)}`
        : contact.url();
});
</script>

<template>
    <Head :title="opportunity.title">
        <meta name="description" :content="opportunity.summary" />
    </Head>

    <div>
        <PageHeader
            :eyebrow="opportunity.type_label"
            :title="opportunity.title"
            :breadcrumbs="[
                { title: 'Get Involved', href: getInvolved.index.url() },
                {
                    title: 'Opportunities',
                    href: getInvolved.opportunities.url(),
                },
            ]"
        >
            <p>{{ opportunity.summary }}</p>
            <template v-if="!opportunity.is_closed" #actions>
                <SiteButton :href="applyHref" variant="donate" size="lg">
                    Apply Now
                </SiteButton>
            </template>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-8">
                    <p
                        v-if="opportunity.is_closed"
                        class="mb-10 flex items-start gap-3 border-l-4 border-terracotta-600 bg-cream px-5 py-4 font-semibold text-ink"
                        role="status"
                    >
                        <CircleAlert
                            class="mt-0.5 size-5 shrink-0 text-terracotta-600"
                        />
                        This opportunity closed on
                        {{ opportunity.closes_on }} and is no longer accepting
                        applications.
                    </p>
                    <div v-if="opportunity.body.length" class="site-prose">
                        <p
                            v-for="paragraph in opportunity.body"
                            :key="paragraph"
                            class="whitespace-pre-line"
                        >
                            {{ paragraph }}
                        </p>
                    </div>
                    <SiteButton
                        :href="getInvolved.opportunities.url()"
                        variant="outline"
                        class="mt-10"
                    >
                        See All Opportunities
                    </SiteButton>
                </div>

                <aside class="lg:col-span-4">
                    <div class="border-t-[5px] border-sun-400 bg-cream p-6">
                        <h2
                            class="font-display text-xl font-bold tracking-[0.06em] text-brand-800 uppercase"
                        >
                            At a glance
                        </h2>
                        <dl class="mt-4 space-y-3 text-stone-700">
                            <div v-if="opportunity.location" class="flex gap-2">
                                <dt>
                                    <MapPin
                                        class="mt-0.5 size-4 text-sun-600"
                                    />
                                    <span class="sr-only">Location</span>
                                </dt>
                                <dd>{{ opportunity.location }}</dd>
                            </div>
                            <div class="flex gap-2">
                                <dt>
                                    <Clock class="mt-0.5 size-4 text-sun-600" />
                                    <span class="sr-only">Closing date</span>
                                </dt>
                                <dd>
                                    {{
                                        opportunity.closes_on
                                            ? `Closes ${opportunity.closes_on}`
                                            : 'Open until filled'
                                    }}
                                </dd>
                            </div>
                        </dl>
                        <div class="mt-6 flex flex-col items-start gap-3">
                            <SiteButton
                                v-if="opportunity.attachment_url"
                                :href="opportunity.attachment_url"
                                variant="outline"
                            >
                                <Download />
                                Full details
                                <template v-if="opportunity.attachment_size">
                                    ({{
                                        opportunity.attachment_size
                                    }})</template
                                >
                            </SiteButton>
                            <SiteButton
                                v-if="!opportunity.is_closed"
                                :href="applyHref"
                            >
                                Apply Now
                            </SiteButton>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</template>
