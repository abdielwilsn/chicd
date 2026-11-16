<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Newspaper } from '@lucide/vue';
import EmptyState from '@/components/site/EmptyState.vue';
import NewsletterForm from '@/components/site/NewsletterForm.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import { siteImages } from '@/lib/siteContent';
import resources from '@/routes/resources';

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
        accent: 'border-lagoon-600',
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

        <section class="border-t border-stone-200 py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="Featured Stories" />
                <EmptyState
                    :icon="Newspaper"
                    title="Articles and updates will appear here as they are published"
                    class="mt-10"
                >
                    <p>
                        Subscribe below to be told when we publish our first
                        stories.
                    </p>
                </EmptyState>
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
