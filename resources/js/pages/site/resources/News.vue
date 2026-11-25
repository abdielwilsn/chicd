<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, MapPin, Newspaper } from '@lucide/vue';
import EmptyState from '@/components/site/EmptyState.vue';
import NewsletterForm from '@/components/site/NewsletterForm.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PostCard from '@/components/site/PostCard.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import { siteImages } from '@/lib/siteContent';
import resources from '@/routes/resources';
import type { Option, Pagination, Post } from '@/types';

defineProps<{
    posts: Post[];
    pagination: Pagination;
    upcomingEvents: Post[];
    filters: { type: string | null };
    typeOptions: Option[];
}>();

const categories = [
    {
        title: 'Programme Updates',
        description:
            'Follow developments from Show Up Ready to Learn, the Safe Housing Programme and other CHICD initiatives.',
        accent: 'border-sun-400',
    },
    {
        title: 'Community Stories',
        description:
            'Discover experiences and perspectives that highlight community needs, resilience and opportunities for change.',
        accent: 'border-terracotta-600',
    },
    {
        title: 'Research & Insights',
        description:
            'Read articles, research commentary and evidence-informed perspectives on health, behaviour and community development.',
        accent: 'border-navy-600',
    },
    {
        title: 'News & Announcements',
        description:
            "Stay informed about CHICD's activities, opportunities, events and organisational developments.",
        accent: 'border-brand-600',
    },
];
</script>

<template>
    <Head title="News & Stories" />

    <div>
        <PageHeader
            :image="siteImages.agriculture"
            eyebrow="News & Stories"
            title="Stories, Updates and Perspectives From CHICD"
            :breadcrumbs="[
                { title: 'Resources', href: resources.research.url() },
                { title: 'News & Stories', href: resources.news.url() },
            ]"
        >
            <p>
                Explore our programmes, community initiatives, research insights
                and organisational updates.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="Explore Our Stories" />
                <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <li
                        v-for="category in categories"
                        :key="category.title"
                        class="border-t-[5px] bg-cream/60 p-6"
                        :class="category.accent"
                    >
                        <h3 class="font-heading text-xl font-bold text-ink">
                            {{ category.title }}
                        </h3>
                        <p class="mt-2 leading-relaxed text-stone-700">
                            {{ category.description }}
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <section
            v-if="upcomingEvents.length"
            class="border-t border-stone-200 bg-brand-900 py-20 text-white sm:py-24"
        >
            <div class="site-container">
                <SectionIntro eyebrow="Upcoming" title="Events" inverted />
                <ul class="mt-12 grid gap-6 md:grid-cols-3">
                    <li v-for="event in upcomingEvents" :key="event.id">
                        <Link
                            :href="event.url"
                            class="group flex h-full gap-5 border-t-[5px] border-sun-400 bg-brand-950/40 p-6 hover:bg-brand-950/60"
                        >
                            <span
                                class="flex w-16 shrink-0 flex-col items-center bg-sun-400 py-2 text-brand-950"
                                aria-hidden="true"
                            >
                                <span
                                    class="font-display text-3xl leading-none font-extrabold"
                                    >{{ event.event_day }}</span
                                >
                                <span
                                    class="font-display text-sm font-bold tracking-[0.12em] uppercase"
                                    >{{ event.event_month }}</span
                                >
                            </span>
                            <span>
                                <span
                                    class="block font-heading text-lg leading-snug font-bold group-hover:underline"
                                    >{{ event.title }}</span
                                >
                                <span class="mt-1 block text-brand-100">{{
                                    event.event_starts
                                }}</span>
                                <span
                                    v-if="event.event_location"
                                    class="mt-1 flex items-center gap-1.5 text-brand-100"
                                >
                                    <MapPin class="size-4 text-sun-300" />
                                    {{ event.event_location }}
                                </span>
                            </span>
                        </Link>
                    </li>
                </ul>
            </div>
        </section>

        <section
            id="stories"
            class="scroll-mt-28 border-t border-stone-200 py-20 sm:py-24"
        >
            <div class="site-container">
                <SectionIntro title="Latest From CHICD" />

                <nav aria-label="Filter stories" class="mt-8">
                    <ul class="flex flex-wrap gap-2">
                        <li>
                            <Link
                                :href="resources.news.url()"
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
                                    resources.news.url({
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
                    v-if="posts.length"
                    class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <li v-for="post in posts" :key="post.id">
                        <PostCard :post="post" />
                    </li>
                </ul>
                <EmptyState
                    v-else
                    :icon="Newspaper"
                    title="Articles and updates will appear here as they are published"
                    class="mt-10"
                >
                    <p>
                        Subscribe below to be told when we publish new stories.
                    </p>
                </EmptyState>

                <nav
                    v-if="pagination.lastPage > 1"
                    aria-label="Pages"
                    class="mt-12 flex items-center justify-between gap-4 border-t border-stone-200 pt-6"
                >
                    <Link
                        v-if="pagination.previousUrl"
                        :href="pagination.previousUrl"
                        class="inline-flex items-center gap-1 font-bold text-brand-700 hover:underline"
                    >
                        <ChevronLeft class="size-4 text-sun-500" />
                        Newer
                    </Link>
                    <span v-else />
                    <span class="text-stone-600"
                        >Page {{ pagination.currentPage }} of
                        {{ pagination.lastPage }}</span
                    >
                    <Link
                        v-if="pagination.nextUrl"
                        :href="pagination.nextUrl"
                        class="inline-flex items-center gap-1 font-bold text-brand-700 hover:underline"
                    >
                        Older
                        <ChevronRight class="size-4 text-sun-500" />
                    </Link>
                    <span v-else />
                </nav>
            </div>
        </section>

        <section id="newsletter" class="scroll-mt-28 bg-sun-400">
            <div
                class="site-container grid gap-8 py-14 sm:py-16 lg:grid-cols-12 lg:items-center lg:gap-12"
            >
                <div class="lg:col-span-5">
                    <h2
                        class="font-heading text-[1.6rem] leading-tight font-bold text-brand-950 sm:text-[1.9rem]"
                    >
                        Stay Informed
                    </h2>
                    <p class="mt-3 leading-relaxed text-brand-950/85">
                        Subscribe to receive programme updates, new
                        publications, opportunities and news from CHICD.
                    </p>
                </div>
                <div class="lg:col-span-7">
                    <NewsletterForm />
                </div>
            </div>
        </section>
    </div>
</template>
