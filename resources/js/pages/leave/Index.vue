<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarPlus, Palmtree } from '@lucide/vue';
import AdminField from '@/components/admin/AdminField.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { Spinner } from '@/components/ui/spinner';
import { cancel, index, store } from '@/routes/leave';
import type { LeaveRequest, Option } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'My leave', href: index() }],
    },
});

const props = defineProps<{
    leaveRequests: LeaveRequest[];
    types: Option[];
    today: string;
}>();

const form = useForm({
    type: props.types[0]?.value ?? 'annual',
    starts_on: props.today,
    ends_on: props.today,
    reason: '',
});

const requestLeave = (): void => {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => form.reset('reason'),
    });
};

const cancelRequest = (leaveRequest: LeaveRequest): void => {
    if (!window.confirm(`Cancel your request for ${leaveRequest.dates}?`)) {
        return;
    }

    router.delete(cancel.url(leaveRequest.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="My leave" />

    <div>
        <PortalPageHeader title="My leave">
            <p>
                Ask for leave here. Once an admin approves it, those days count
                as leave on your timesheet instead of absence.
            </p>
        </PortalPageHeader>

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <PortalPanel title="Request leave" :icon="CalendarPlus">
                <form
                    class="grid gap-5 sm:grid-cols-2"
                    @submit.prevent="requestLeave"
                >
                    <AdminField
                        id="leave-type"
                        label="Type of leave"
                        :error="form.errors.type"
                        class="sm:col-span-2"
                    >
                        <select
                            id="leave-type"
                            v-model="form.type"
                            class="portal-input"
                        >
                            <option
                                v-for="type in types"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                    </AdminField>
                    <AdminField
                        id="leave-starts-on"
                        label="First day"
                        :error="form.errors.starts_on"
                    >
                        <input
                            id="leave-starts-on"
                            v-model="form.starts_on"
                            type="date"
                            required
                            class="portal-input"
                            :aria-invalid="!!form.errors.starts_on"
                        />
                    </AdminField>
                    <AdminField
                        id="leave-ends-on"
                        label="Last day"
                        :error="form.errors.ends_on"
                    >
                        <input
                            id="leave-ends-on"
                            v-model="form.ends_on"
                            type="date"
                            required
                            :min="form.starts_on"
                            class="portal-input"
                            :aria-invalid="!!form.errors.ends_on"
                        />
                    </AdminField>
                    <AdminField
                        id="leave-reason"
                        label="Note for your manager"
                        note="(optional)"
                        :error="form.errors.reason"
                        class="sm:col-span-2"
                    >
                        <textarea
                            id="leave-reason"
                            v-model="form.reason"
                            rows="3"
                            class="portal-input resize-y"
                        />
                    </AdminField>
                    <div class="sm:col-span-2">
                        <SiteButton type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            Send request
                        </SiteButton>
                    </div>
                </form>
            </PortalPanel>

            <section class="space-y-3">
                <h2 class="font-heading text-2xl font-bold">Your requests</h2>
                <ul
                    v-if="leaveRequests.length"
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <li
                        v-for="leaveRequest in leaveRequests"
                        :key="leaveRequest.id"
                        class="space-y-1 px-4 py-4"
                    >
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1"
                        >
                            <StatusBadge
                                :status="leaveRequest.status"
                                :label="leaveRequest.status_label"
                            />
                            <span class="font-heading font-bold">{{
                                leaveRequest.dates
                            }}</span>
                            <span class="text-muted-foreground">{{
                                leaveRequest.type_label
                            }}</span>
                            <button
                                v-if="leaveRequest.status === 'pending'"
                                type="button"
                                class="ml-auto font-semibold text-destructive hover:underline"
                                @click="cancelRequest(leaveRequest)"
                            >
                                Cancel request
                            </button>
                        </div>
                        <p
                            v-if="leaveRequest.reason"
                            class="text-[0.95rem] text-muted-foreground"
                        >
                            {{ leaveRequest.reason }}
                        </p>
                        <p
                            v-if="leaveRequest.decision_note"
                            class="text-[0.95rem]"
                        >
                            <span class="font-semibold"
                                >{{ leaveRequest.decided_by ?? 'Admin' }}:</span
                            >
                            {{ leaveRequest.decision_note }}
                        </p>
                    </li>
                </ul>
                <PortalEmptyState v-else :icon="Palmtree" title="No leave yet">
                    Requests you send appear here with their status.
                </PortalEmptyState>
            </section>
        </div>
    </div>
</template>
