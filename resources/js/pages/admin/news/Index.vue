<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Newspaper, SquarePen } from '@lucide/vue';
import AdminRowActions from '@/components/admin/AdminRowActions.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { create, destroy, edit, index } from '@/routes/admin/news';
import type { AdminPost } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'News & events', href: index() }],
    },
});

defineProps<{
    posts: AdminPost[];
}>();
</script>

<template>
    <Head title="News & events" />

    <div>
        <PortalPageHeader eyebrow="Admin · Website" title="News & events">
            <p>
                Stories, news, events and announcements on News &amp; Stories.
                The latest also appear on Our Work.
            </p>
            <template #actions>
                <SiteButton :href="create.url()" variant="donate">
                    <SquarePen />
                    Write a post
                </SiteButton>
            </template>
        </PortalPageHeader>

        <div class="portal-container py-8 sm:py-10">
            <div
                v-if="posts.length"
                class="overflow-x-auto border-y border-border bg-card"
            >
                <table class="w-full min-w-[48rem] text-left">
                    <thead
                        class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="post in posts" :key="post.id">
                            <td class="px-4 py-3">
                                <a
                                    v-if="post.is_published"
                                    :href="post.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="font-heading font-bold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                                    >{{ post.title }}</a
                                >
                                <span v-else class="font-heading font-bold">{{
                                    post.title
                                }}</span>
                                <span
                                    v-if="post.event_starts"
                                    class="block text-[0.9rem] text-muted-foreground"
                                    >{{ post.event_starts }}</span
                                >
                            </td>
                            <td class="px-4 py-3">{{ post.type_label }}</td>
                            <td class="px-4 py-3">
                                <StatusBadge
                                    :status="
                                        post.is_published
                                            ? 'approved'
                                            : 'pending'
                                    "
                                    :label="
                                        post.is_published
                                            ? 'Published'
                                            : 'Draft'
                                    "
                                />
                                <span
                                    v-if="post.published_on"
                                    class="block text-[0.85rem] text-muted-foreground"
                                    >{{ post.published_on }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                <AdminRowActions
                                    :edit-href="edit.url(post.id)"
                                    :destroy-url="destroy.url(post.id)"
                                    :confirm-message="`Remove &quot;${post.title}&quot; from the website?`"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PortalEmptyState
                v-else
                :icon="Newspaper"
                title="Nothing posted yet"
            >
                Write the first story, news update, event or announcement.
            </PortalEmptyState>
        </div>
    </div>
</template>
