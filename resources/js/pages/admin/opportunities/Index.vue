<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BriefcaseBusiness, FilePlus2 } from '@lucide/vue';
import AdminRowActions from '@/components/admin/AdminRowActions.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { create, destroy, edit, index } from '@/routes/admin/opportunities';
import type { AdminOpportunity } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Opportunities', href: index() }],
    },
});

defineProps<{
    opportunities: AdminOpportunity[];
}>();

function status(opportunity: AdminOpportunity): {
    status: string;
    label: string;
} {
    if (!opportunity.is_published) {
        return { status: 'pending', label: 'Draft' };
    }

    if (opportunity.is_closed) {
        return { status: 'declined', label: 'Closed' };
    }

    return { status: 'approved', label: 'Open' };
}
</script>

<template>
    <Head title="Opportunities" />

    <div>
        <PortalPageHeader eyebrow="Admin · Website" title="Opportunities">
            <p>
                Volunteer roles, research opportunities, internships, vacancies
                and consultancies. Open opportunities are listed on Get
                Involved; they stop being listed after their closing date.
            </p>
            <template #actions>
                <SiteButton :href="create.url()" variant="donate">
                    <FilePlus2 />
                    Add an opportunity
                </SiteButton>
            </template>
        </PortalPageHeader>

        <div class="portal-container py-8 sm:py-10">
            <div
                v-if="opportunities.length"
                class="overflow-x-auto border-y border-border bg-card"
            >
                <table class="w-full min-w-[48rem] text-left">
                    <thead
                        class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Closes</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="opportunity in opportunities"
                            :key="opportunity.id"
                        >
                            <td class="px-4 py-3">
                                <a
                                    v-if="opportunity.is_published"
                                    :href="opportunity.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="font-heading font-bold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                                    >{{ opportunity.title }}</a
                                >
                                <span v-else class="font-heading font-bold">{{
                                    opportunity.title
                                }}</span>
                                <span
                                    v-if="opportunity.location"
                                    class="block text-[0.9rem] text-muted-foreground"
                                    >{{ opportunity.location }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                {{ opportunity.type_label }}
                            </td>
                            <td class="px-4 py-3">
                                {{ opportunity.closes_on ?? 'No closing date' }}
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge v-bind="status(opportunity)" />
                            </td>
                            <td class="px-4 py-3">
                                <AdminRowActions
                                    :edit-href="edit.url(opportunity.id)"
                                    :destroy-url="destroy.url(opportunity.id)"
                                    :confirm-message="`Remove &quot;${opportunity.title}&quot; from the website?`"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PortalEmptyState
                v-else
                :icon="BriefcaseBusiness"
                title="No opportunities yet"
            >
                Add a volunteer role, vacancy or consultancy to advertise it on
                the website.
            </PortalEmptyState>
        </div>
    </div>
</template>
