<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FolderPlus, LayoutGrid } from '@lucide/vue';
import AdminRowActions from '@/components/admin/AdminRowActions.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { create, destroy, edit, index } from '@/routes/admin/programmes';
import type { AdminProgramme } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Programmes', href: index() }],
    },
});

defineProps<{
    programmes: AdminProgramme[];
}>();
</script>

<template>
    <Head title="Programmes" />

    <div>
        <PortalPageHeader eyebrow="Admin · Website" title="Programmes">
            <p>
                Programmes added here appear on the home page and Our Work,
                after Show Up Ready to Learn and the Safe Housing Programme, and
                each gets its own page.
            </p>
            <template #actions>
                <SiteButton :href="create.url()" variant="donate">
                    <FolderPlus />
                    Add a programme
                </SiteButton>
            </template>
        </PortalPageHeader>

        <div class="portal-container py-8 sm:py-10">
            <div
                v-if="programmes.length"
                class="overflow-x-auto border-y border-border bg-card"
            >
                <table class="w-full min-w-[44rem] text-left">
                    <thead
                        class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3">Programme</th>
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="programme in programmes" :key="programme.id">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="programme.image.src"
                                        alt=""
                                        class="size-12 shrink-0 object-cover"
                                    />
                                    <div>
                                        <a
                                            v-if="programme.is_published"
                                            :href="programme.url"
                                            target="_blank"
                                            rel="noopener"
                                            class="font-heading font-bold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                                            >{{ programme.name }}</a
                                        >
                                        <span
                                            v-else
                                            class="font-heading font-bold"
                                            >{{ programme.name }}</span
                                        >
                                        <span
                                            v-if="programme.tagline"
                                            class="block text-[0.9rem] text-muted-foreground"
                                            >{{ programme.tagline }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ programme.sort_order }}
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge
                                    :status="
                                        programme.is_published
                                            ? 'approved'
                                            : 'pending'
                                    "
                                    :label="
                                        programme.is_published
                                            ? 'Published'
                                            : 'Draft'
                                    "
                                />
                            </td>
                            <td class="px-4 py-3">
                                <AdminRowActions
                                    :edit-href="edit.url(programme.id)"
                                    :destroy-url="destroy.url(programme.id)"
                                    :confirm-message="`Remove &quot;${programme.name}&quot; from the website? Its page and photograph will be deleted.`"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PortalEmptyState
                v-else
                :icon="LayoutGrid"
                title="No programmes added yet"
            >
                Show Up Ready to Learn and the Safe Housing Programme are always
                shown. Add a new programme when CHICD launches one.
            </PortalEmptyState>
        </div>
    </div>
</template>
