<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, SearchX } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { computed, reactive } from 'vue';
import EmptyState from '@/components/site/EmptyState.vue';
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
    filters: { search: string; category: string | null; year: number | null };
    categoryOptions: { value: string; label: string }[];
    yearOptions: number[];
};

const props = defineProps<Props>();

const filters = reactive({
    search: props.filters.search,
    category: props.filters.category ?? '',
    year: props.filters.year ? String(props.filters.year) : '',
});

const isFiltered = computed(
    () =>
        !!(
            props.filters.search ||
            props.filters.category ||
            props.filters.year
        ),
);

function applyFilters(): void {
    router.get(
        resources.publications.url({
            query: {
                search: filters.search.trim() || undefined,
                category: filters.category || undefined,
                year: filters.year || undefined,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearFilters(): void {
    filters.search = '';
    filters.category = '';
    filters.year = '';
    applyFilters();
}

watchDebounced(() => filters.search, applyFilters, { debounce: 350 });
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
                <form
                    role="search"
                    aria-label="Search publications"
                    class="mb-12 grid gap-4 border-t-[5px] border-sun-400 bg-cream p-5 sm:grid-cols-[minmax(0,1fr)_14rem_9rem_auto] sm:items-end sm:p-6"
                    @submit.prevent="applyFilters"
                >
                    <div>
                        <label for="publication-search" class="site-label-text"
                            >Search</label
                        >
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-stone-500"
                            />
                            <input
                                id="publication-search"
                                v-model="filters.search"
                                type="search"
                                maxlength="100"
                                placeholder="Title or topic"
                                class="site-input pl-11"
                            />
                        </div>
                    </div>
                    <div>
                        <label
                            for="publication-category"
                            class="site-label-text"
                            >Type</label
                        >
                        <select
                            id="publication-category"
                            v-model="filters.category"
                            class="site-input"
                            @change="applyFilters"
                        >
                            <option value="">All types</option>
                            <option
                                v-for="option in categoryOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="publication-year" class="site-label-text"
                            >Year</label
                        >
                        <select
                            id="publication-year"
                            v-model="filters.year"
                            class="site-input"
                            @change="applyFilters"
                        >
                            <option value="">Any year</option>
                            <option
                                v-for="year in yearOptions"
                                :key="year"
                                :value="String(year)"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    <SiteButton
                        v-if="isFiltered"
                        variant="link"
                        class="sm:mb-3"
                        @click="clearFilters"
                        >Clear</SiteButton
                    >
                </form>

                <EmptyState
                    v-if="isFiltered && !groups.length"
                    :icon="SearchX"
                    title="No documents match your search"
                >
                    Try a different word, or clear the filters to see
                    everything.
                </EmptyState>

                <PublicationLibrary
                    v-else
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
