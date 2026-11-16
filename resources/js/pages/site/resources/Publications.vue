<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/site/PageHeader.vue';
import PublicationLibrary from '@/components/site/PublicationLibrary.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { newsletterAnchor } from '@/lib/siteContent';
import policies from '@/routes/policies';
import resources from '@/routes/resources';
import type { Publication, PublicationGroup } from '@/types';

type Props = {
    featuredAnnualReport: Publication | null;
    groups: PublicationGroup[];
};

defineProps<Props>();
</script>

<template>
    <Head title="Publications & Reports" />

    <div>
        <PageHeader
            eyebrow="Resources"
            title="Publications & Reports"
            :breadcrumbs="[
                { title: 'Resources', href: resources.research.url() },
                {
                    title: 'Publications & Reports',
                    href: resources.publications.url(),
                },
            ]"
        >
            <p>
                Download CHICD's annual reports, research, policy briefs,
                programme evaluations, toolkits and other resources.
            </p>
        </PageHeader>

        <section class="py-16 sm:py-20">
            <div class="site-container">
                <PublicationLibrary
                    :featured-annual-report="featuredAnnualReport"
                    :groups="groups"
                >
                    <template #empty>
                        <p>
                            CHICD was registered in July 2026. Our first annual
                            report and publications will be available to
                            download here.
                        </p>
                    </template>
                </PublicationLibrary>

                <div
                    class="mt-16 flex flex-col gap-5 border-t-[5px] border-brand-600 bg-cream p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8"
                >
                    <p class="font-heading text-lg font-semibold text-ink">
                        Looking for our safeguarding, privacy and governance
                        documents?
                    </p>
                    <div class="flex flex-wrap items-center gap-6">
                        <SiteButton
                            :href="policies.index.url()"
                            variant="outline"
                            size="sm"
                        >
                            Policies &amp; Accountability
                        </SiteButton>
                        <SiteButton :href="newsletterAnchor" variant="link">
                            Get new publications by email
                        </SiteButton>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
