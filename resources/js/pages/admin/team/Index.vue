<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { UserPlus, Users } from '@lucide/vue';
import AdminRowActions from '@/components/admin/AdminRowActions.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { create, destroy, edit, index } from '@/routes/admin/team';
import type { AdminTeamMember } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Team page', href: index() }],
    },
});

defineProps<{
    members: AdminTeamMember[];
}>();
</script>

<template>
    <Head title="Team page" />

    <div>
        <PortalPageHeader eyebrow="Admin · Website" title="Team page">
            <p>
                The people shown on the Leadership &amp; Team page. This list is
                separate from staff portal accounts.
            </p>
            <template #actions>
                <SiteButton :href="create.url()" variant="donate">
                    <UserPlus />
                    Add a person
                </SiteButton>
            </template>
        </PortalPageHeader>

        <div class="portal-container py-8 sm:py-10">
            <div
                v-if="members.length"
                class="overflow-x-auto border-y border-border bg-card"
            >
                <table class="w-full min-w-[44rem] text-left">
                    <thead
                        class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Section</th>
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="member in members" :key="member.id">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="member.photo_url"
                                        :src="member.photo_url"
                                        alt=""
                                        class="size-11 shrink-0 rounded-full object-cover"
                                    />
                                    <span
                                        v-else
                                        class="flex size-11 shrink-0 items-center justify-center rounded-full bg-secondary font-display font-bold"
                                        aria-hidden="true"
                                        >{{ member.initials }}</span
                                    >
                                    <div>
                                        <span class="font-heading font-bold">{{
                                            member.name
                                        }}</span>
                                        <span
                                            class="block text-[0.9rem] text-muted-foreground"
                                            >{{ member.position }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                {{ member.section_label }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ member.sort_order }}
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge
                                    :status="
                                        member.is_published
                                            ? 'approved'
                                            : 'pending'
                                    "
                                    :label="
                                        member.is_published ? 'Shown' : 'Hidden'
                                    "
                                />
                            </td>
                            <td class="px-4 py-3">
                                <AdminRowActions
                                    :edit-href="edit.url(member.id)"
                                    :destroy-url="destroy.url(member.id)"
                                    :confirm-message="`Remove ${member.name} from the team page?`"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PortalEmptyState v-else :icon="Users" title="No one listed yet">
                Add the executive leadership and team members to show them on
                the Leadership &amp; Team page.
            </PortalEmptyState>
        </div>
    </div>
</template>
