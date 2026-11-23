<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BriefcaseBusiness } from '@lucide/vue';
import EmptyState from '@/components/site/EmptyState.vue';
import NewsletterForm from '@/components/site/NewsletterForm.vue';
import OpportunityCard from '@/components/site/OpportunityCard.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import getInvolved from '@/routes/get-involved';
import type { Opportunity, Option } from '@/types';

defineProps<{
    opportunities: Opportunity[];
    filters: { type: string | null };
    typeOptions: Option[];
}>();
</script>

<template>
    <Head title="Opportunities">
        <meta
            name="description"
            content="Volunteer roles, research opportunities, internships, vacancies and consultancies at CHICD."
        />
    </Head>

    <div>
        <PageHeader
            :image="siteImages.education"
            eyebrow="Get Involved"
            title="Opportunities"
            :breadcrumbs="[
                { title: 'Get Involved', href: getInvolved.index.url() },
                {
                    title: 'Opportunities',
                    href: getInvolved.opportunities.url(),
                },
            ]"
        >
            <p>
                Volunteer roles, research opportunities, internships, vacancies
                and consultancies with CHICD.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <nav aria-label="Filter opportunities">
                    <ul class="flex flex-wrap gap-2">
                        <li>
                            <Link
                                :href="getInvolved.opportunities.url()"
                                preserve-scroll
                                class="inline-block border-2 px-4 py-1.5 font-semibold"
                                :class="
                                    filters.type === null
                                        ? 'border-brand-700 bg-brand-700 text-white'
                                        : 'border-stone-300 text-brand-800 hover:border-brand-700'
                                "
                                :aria-current="
                                    filters.type === null ? 'page' : undefined
                                "
                                >All</Link
                            >
                        </li>
                        <li v-for="option in typeOptions" :key="option.value">
                            <Link
                                :href="
                                    getInvolved.opportunities.url({
                                        query: { type: option.value },
                                    })
                                "
                                preserve-scroll
                                class="inline-block border-2 px-4 py-1.5 font-semibold"
                                :class="
                                    filters.type === option.value
                                        ? 'border-brand-700 bg-brand-700 text-white'
                                        : 'border-stone-300 text-brand-800 hover:border-brand-700'
                                "
                                :aria-current="
                                    filters.type === option.value
                                        ? 'page'
                                        : undefined
                                "
                                >{{ option.label }}</Link
                            >
                        </li>
                    </ul>
                </nav>

                <ul
                    v-if="opportunities.length"
                    class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="opportunity in opportunities"
                        :key="opportunity.id"
                    >
                        <OpportunityCard :opportunity="opportunity" />
                    </li>
                </ul>
                <EmptyState
                    v-else
                    :icon="BriefcaseBusiness"
                    title="There are no open opportunities right now"
                    class="mt-10"
                >
                    <p>
                        New roles are advertised here and in our newsletter. You
                        can still register your interest in volunteering.
                    </p>
                    <template #actions>
                        <SiteButton
                            :href="`${getInvolved.volunteer.url()}#apply`"
                            variant="link"
                        >
                            Apply to Volunteer
                        </SiteButton>
                    </template>
                </EmptyState>
            </div>
        </section>

        <section class="bg-sun-400">
            <div
                class="site-container grid gap-8 py-14 sm:py-16 lg:grid-cols-12 lg:items-center lg:gap-12"
            >
                <div class="lg:col-span-5">
                    <h2
                        class="font-heading text-[1.6rem] leading-tight font-bold text-brand-950 sm:text-[1.9rem]"
                    >
                        Hear about new opportunities
                    </h2>
                    <p class="mt-3 leading-relaxed text-brand-950/85">
                        Subscribe to receive new opportunities, programme
                        updates and news from CHICD.
                    </p>
                </div>
                <div class="lg:col-span-7">
                    <NewsletterForm />
                </div>
            </div>
        </section>
    </div>
</template>
