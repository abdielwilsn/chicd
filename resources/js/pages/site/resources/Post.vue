<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarDays, MapPin } from '@lucide/vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PostCard from '@/components/site/PostCard.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import resources from '@/routes/resources';
import type { Post } from '@/types';

defineProps<{
    post: Post;
    morePosts: Post[];
}>();
</script>

<template>
    <Head :title="post.title">
        <meta name="description" :content="post.excerpt" />
    </Head>

    <div>
        <PageHeader
            :image="post.cover_image ?? undefined"
            :eyebrow="post.type_label"
            :title="post.title"
            :breadcrumbs="[
                { title: 'Resources', href: resources.research.url() },
                { title: 'News & Stories', href: resources.news.url() },
            ]"
        >
            <p v-if="post.published_on">Published {{ post.published_on }}</p>
        </PageHeader>

        <article class="py-16 sm:py-20">
            <div class="site-container">
                <div class="max-w-3xl">
                    <div
                        v-if="post.event_starts"
                        class="mb-10 space-y-2 border-l-4 border-sun-400 bg-cream px-5 py-4 font-semibold text-ink"
                    >
                        <p class="flex items-start gap-2">
                            <CalendarDays
                                class="mt-0.5 size-5 shrink-0 text-sun-600"
                            />
                            {{ post.event_starts }}
                        </p>
                        <p
                            v-if="post.event_location"
                            class="flex items-start gap-2"
                        >
                            <MapPin
                                class="mt-0.5 size-5 shrink-0 text-sun-600"
                            />
                            {{ post.event_location }}
                        </p>
                    </div>
                    <div class="site-prose">
                        <p
                            v-for="paragraph in post.body"
                            :key="paragraph"
                            class="whitespace-pre-line"
                        >
                            {{ paragraph }}
                        </p>
                    </div>
                    <SiteButton
                        :href="resources.news.url()"
                        variant="outline"
                        class="mt-12"
                    >
                        Back to News &amp; Stories
                    </SiteButton>
                </div>
            </div>
        </article>

        <section
            v-if="morePosts.length"
            class="border-t border-stone-200 bg-cream/40 py-20 sm:py-24"
        >
            <div class="site-container">
                <SectionIntro title="More From CHICD" />
                <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <li v-for="item in morePosts" :key="item.id">
                        <PostCard :post="item" />
                    </li>
                </ul>
            </div>
        </section>
    </div>
</template>
