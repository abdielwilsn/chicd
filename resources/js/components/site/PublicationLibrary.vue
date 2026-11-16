<script setup lang="ts">
import { Download, FileText, Library } from '@lucide/vue';
import EmptyState from '@/components/site/EmptyState.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import type { Publication, PublicationGroup } from '@/types';

type Props = {
    featuredAnnualReport: Publication | null;
    groups: PublicationGroup[];
    emptyTitle?: string;
};

withDefaults(defineProps<Props>(), {
    emptyTitle: 'Documents will appear here as they are published',
});
</script>

<template>
    <div class="space-y-16">
        <article
            v-if="featuredAnnualReport"
            class="grid border-t-[6px] border-sun-400 bg-cream md:grid-cols-[minmax(0,17rem)_1fr]"
        >
            <div class="flex items-center justify-center bg-brand-900 p-8">
                <img
                    v-if="featuredAnnualReport.cover_image_url"
                    :src="featuredAnnualReport.cover_image_url"
                    :alt="`Cover of ${featuredAnnualReport.title}`"
                    class="aspect-[3/4] w-44 object-cover shadow-2xl md:w-full"
                />
                <div
                    v-else
                    class="flex aspect-[3/4] w-44 flex-col justify-between border-t-[6px] border-sun-400 bg-white p-5 shadow-2xl md:w-full"
                    aria-hidden="true"
                >
                    <span
                        class="font-display text-2xl font-extrabold text-brand-800"
                        >CHICD</span
                    >
                    <span>
                        <span
                            class="block font-display text-sm font-bold tracking-[0.14em] text-sun-600 uppercase"
                            >Annual Report</span
                        >
                        <span
                            class="block font-display text-5xl leading-none font-extrabold text-ink"
                            >{{ featuredAnnualReport.year }}</span
                        >
                    </span>
                </div>
            </div>
            <div class="flex flex-col justify-center p-8 sm:p-10">
                <p class="site-label">Latest annual report</p>
                <h2 class="mt-4 font-heading text-3xl font-bold text-ink">
                    {{ featuredAnnualReport.title }}
                </h2>
                <p
                    v-if="featuredAnnualReport.summary"
                    class="mt-4 max-w-2xl leading-relaxed text-stone-700"
                >
                    {{ featuredAnnualReport.summary }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <SiteButton :href="featuredAnnualReport.file_url" size="lg">
                        <Download />
                        Download report
                    </SiteButton>
                    <span
                        v-if="featuredAnnualReport.file_size"
                        class="text-[0.95rem] text-stone-600"
                        >PDF · {{ featuredAnnualReport.file_size }}</span
                    >
                </div>
            </div>
        </article>

        <section
            v-for="group in groups"
            :key="group.category"
            :aria-labelledby="`group-${group.category}`"
        >
            <h2
                :id="`group-${group.category}`"
                class="site-label text-[1.05rem]"
            >
                {{ group.label }}
            </h2>
            <ul
                class="mt-4 divide-y divide-stone-200 border-b border-stone-200"
            >
                <li
                    v-for="publication in group.items"
                    :key="publication.id"
                    class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center"
                >
                    <FileText class="size-6 shrink-0 text-brand-600" />
                    <div class="min-w-0 flex-1">
                        <h3 class="font-heading text-lg font-bold text-ink">
                            {{ publication.title }}
                        </h3>
                        <p
                            v-if="publication.summary"
                            class="mt-1 line-clamp-2 text-stone-700"
                        >
                            {{ publication.summary }}
                        </p>
                        <p class="mt-1 text-[0.9rem] text-stone-500">
                            {{ publication.year }}
                            <template v-if="publication.file_size">
                                · PDF · {{ publication.file_size }}</template
                            >
                        </p>
                    </div>
                    <a
                        :href="publication.file_url"
                        class="inline-flex h-10 items-center gap-2 self-start rounded-[3px] border-2 border-brand-700 px-4 font-display font-bold tracking-[0.06em] text-brand-800 uppercase hover:bg-brand-700 hover:text-white sm:self-center"
                        target="_blank"
                        rel="noopener"
                    >
                        <Download class="size-4" />
                        Download
                        <span class="sr-only">{{ publication.title }}</span>
                    </a>
                </li>
            </ul>
        </section>

        <EmptyState
            v-if="!featuredAnnualReport && groups.length === 0"
            :icon="Library"
            :title="emptyTitle"
        >
            <slot name="empty" />
        </EmptyState>
    </div>
</template>
