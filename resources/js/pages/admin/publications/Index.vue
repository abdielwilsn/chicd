<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FilePlus2, Library, Pencil, Star, Trash2 } from '@lucide/vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { create, destroy, edit, index } from '@/routes/admin/publications';
import type { AdminPublication } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Publications', href: index() }],
    },
});

defineProps<{
    publications: AdminPublication[];
}>();

function remove(publication: AdminPublication): void {
    if (
        !window.confirm(
            `Remove "${publication.title}" from the website? The file will be deleted.`,
        )
    ) {
        return;
    }

    router.delete(destroy.url(publication.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Publications" />

    <div>
        <PortalPageHeader eyebrow="Admin · Website" title="Publications">
            <p>
                Reports, research, policies and other documents on the website.
                Policies & Accountability shows annual reports, financial
                reports and governance documents.
            </p>
            <template #actions>
                <SiteButton :href="create.url()" variant="donate">
                    <FilePlus2 />
                    Add a publication
                </SiteButton>
            </template>
        </PortalPageHeader>

        <div class="portal-container py-8 sm:py-10">
            <div
                v-if="publications.length"
                class="overflow-x-auto border-y border-border bg-card"
            >
                <table class="w-full min-w-[48rem] text-left">
                    <thead
                        class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Year</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="publication in publications"
                            :key="publication.id"
                        >
                            <td class="px-4 py-3">
                                <a
                                    :href="publication.file_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="font-heading font-bold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                                    >{{ publication.title }}</a
                                >
                                <span
                                    v-if="publication.is_featured"
                                    class="mt-0.5 flex items-center gap-1 text-[0.9rem] text-sun-700 dark:text-sun-300"
                                >
                                    <Star class="size-3.5" />
                                    Featured annual report
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ publication.category_label }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ publication.year }}
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge
                                    :status="
                                        publication.is_published
                                            ? 'approved'
                                            : 'pending'
                                    "
                                    :label="
                                        publication.is_published
                                            ? 'Published'
                                            : 'Draft'
                                    "
                                />
                                <span
                                    v-if="publication.published_on"
                                    class="block text-[0.85rem] text-muted-foreground"
                                    >{{ publication.published_on }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-4">
                                    <Link
                                        :href="edit(publication.id)"
                                        class="inline-flex items-center gap-1.5 font-semibold text-brand-700 hover:underline dark:text-brand-200"
                                    >
                                        <Pencil class="size-4" />
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 font-semibold text-destructive hover:underline"
                                        @click="remove(publication)"
                                    >
                                        <Trash2 class="size-4" />
                                        Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PortalEmptyState
                v-else
                :icon="Library"
                title="No publications yet"
            >
                Add the first annual report, policy or research document.
            </PortalEmptyState>
        </div>
    </div>
</template>
