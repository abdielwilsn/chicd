<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCheck } from '@lucide/vue';
import CorrectionRequestCard from '@/components/staff/CorrectionRequestCard.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import { index } from '@/routes/admin/corrections';
import type { CorrectionRequest } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Correction requests', href: index() }],
    },
});

defineProps<{
    pending: CorrectionRequest[];
    recentlyClosed: CorrectionRequest[];
}>();
</script>

<template>
    <Head title="Correction requests" />

    <div>
        <PortalPageHeader eyebrow="Admin" title="Correction requests">
            <p>Staff requests to correct their attendance, oldest first.</p>
        </PortalPageHeader>

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <section class="space-y-3">
                <h2 class="font-heading text-2xl font-bold">
                    Waiting for a decision
                </h2>
                <ul
                    v-if="pending.length"
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <CorrectionRequestCard
                        v-for="request in pending"
                        :key="request.id"
                        :request="request"
                        link-to-timesheet
                    />
                </ul>
                <PortalEmptyState
                    v-else
                    :icon="CheckCheck"
                    title="Nothing waiting"
                >
                    New requests from staff appear here.
                </PortalEmptyState>
            </section>

            <section v-if="recentlyClosed.length" class="space-y-3">
                <h2 class="font-heading text-2xl font-bold">Recently closed</h2>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <CorrectionRequestCard
                        v-for="request in recentlyClosed"
                        :key="request.id"
                        :request="request"
                        link-to-timesheet
                    />
                </ul>
            </section>
        </div>
    </div>
</template>
