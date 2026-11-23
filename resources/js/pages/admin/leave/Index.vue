<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarOff, CheckCheck } from '@lucide/vue';
import { reactive } from 'vue';
import AdminField from '@/components/admin/AdminField.vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import {
    destroy as destroyHoliday,
    store as storeHoliday,
} from '@/routes/admin/holidays';
import { index, update } from '@/routes/admin/leave';
import type { LeaveRequest } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Leave requests', href: index() }],
    },
});

defineProps<{
    pending: LeaveRequest[];
    recentlyDecided: LeaveRequest[];
    holidays: { id: number; date: string; label: string; name: string }[];
}>();

/** The note typed for each pending request, keyed by request id. */
const notes = reactive<Record<number, string>>({});
const decisionErrors = reactive<Record<number, string | undefined>>({});

const decide = (
    leaveRequest: LeaveRequest,
    status: 'approved' | 'declined',
): void => {
    router.patch(
        update.url(leaveRequest.id),
        { status, note: notes[leaveRequest.id] ?? '' },
        {
            preserveScroll: true,
            onError: (errors) => {
                decisionErrors[leaveRequest.id] = errors.note ?? errors.status;
            },
            onSuccess: () => {
                decisionErrors[leaveRequest.id] = undefined;
            },
        },
    );
};

const holidayForm = useForm({ date: '', name: '' });

const addHoliday = (): void => {
    holidayForm.post(storeHoliday.url(), {
        preserveScroll: true,
        onSuccess: () => holidayForm.reset(),
    });
};

const removeHoliday = (holiday: { id: number; name: string }): void => {
    if (!window.confirm(`Remove ${holiday.name}?`)) {
        return;
    }

    router.delete(destroyHoliday.url(holiday.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Leave requests" />

    <div>
        <PortalPageHeader eyebrow="Admin" title="Leave requests">
            <p>
                Approved leave stops those days counting as absences. Public
                holidays are not working days for anyone.
            </p>
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
                    <li
                        v-for="leaveRequest in pending"
                        :key="leaveRequest.id"
                        class="space-y-3 px-4 py-4"
                    >
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1"
                        >
                            <span class="font-heading font-bold">{{
                                leaveRequest.staff_name
                            }}</span>
                            <span>{{ leaveRequest.dates }}</span>
                            <span class="text-muted-foreground"
                                >{{ leaveRequest.type_label }} ·
                                {{ leaveRequest.calendar_days }}
                                {{
                                    leaveRequest.calendar_days === 1
                                        ? 'day'
                                        : 'days'
                                }}</span
                            >
                            <span
                                class="ml-auto text-[0.9rem] text-muted-foreground"
                                >asked {{ leaveRequest.requested_at }}</span
                            >
                        </div>
                        <p
                            v-if="leaveRequest.reason"
                            class="text-[0.95rem] text-muted-foreground"
                        >
                            {{ leaveRequest.reason }}
                        </p>
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="min-w-64 flex-1">
                                <label
                                    :for="`leave-note-${leaveRequest.id}`"
                                    class="portal-label-text"
                                    >Note for {{ leaveRequest.staff_name }}
                                    <span
                                        class="font-normal text-muted-foreground"
                                        >(needed when declining)</span
                                    ></label
                                >
                                <input
                                    :id="`leave-note-${leaveRequest.id}`"
                                    v-model="notes[leaveRequest.id]"
                                    type="text"
                                    class="portal-input"
                                />
                            </div>
                            <SiteButton
                                type="button"
                                size="sm"
                                @click="decide(leaveRequest, 'approved')"
                            >
                                Approve
                            </SiteButton>
                            <SiteButton
                                type="button"
                                size="sm"
                                variant="outline"
                                @click="decide(leaveRequest, 'declined')"
                            >
                                Decline
                            </SiteButton>
                        </div>
                        <InputError
                            :message="decisionErrors[leaveRequest.id]"
                        />
                    </li>
                </ul>
                <PortalEmptyState
                    v-else
                    :icon="CheckCheck"
                    title="Nothing waiting"
                >
                    New leave requests from staff appear here.
                </PortalEmptyState>
            </section>

            <section v-if="recentlyDecided.length" class="space-y-3">
                <h2 class="font-heading text-2xl font-bold">
                    Recently decided
                </h2>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <li
                        v-for="leaveRequest in recentlyDecided"
                        :key="leaveRequest.id"
                        class="flex flex-wrap items-center gap-x-3 gap-y-1 px-4 py-3"
                    >
                        <StatusBadge
                            :status="leaveRequest.status"
                            :label="leaveRequest.status_label"
                        />
                        <span class="font-heading font-bold">{{
                            leaveRequest.staff_name
                        }}</span>
                        <span>{{ leaveRequest.dates }}</span>
                        <span class="text-muted-foreground">{{
                            leaveRequest.type_label
                        }}</span>
                        <span
                            v-if="leaveRequest.decided_by"
                            class="ml-auto text-[0.9rem] text-muted-foreground"
                            >by {{ leaveRequest.decided_by }}</span
                        >
                    </li>
                </ul>
            </section>

            <PortalPanel title="Public holidays" :icon="CalendarOff">
                <form
                    class="grid gap-4 sm:grid-cols-[12rem_1fr_auto] sm:items-end"
                    @submit.prevent="addHoliday"
                >
                    <AdminField
                        id="holiday-date"
                        label="Date"
                        :error="holidayForm.errors.date"
                    >
                        <input
                            id="holiday-date"
                            v-model="holidayForm.date"
                            type="date"
                            required
                            class="portal-input"
                        />
                    </AdminField>
                    <AdminField
                        id="holiday-name"
                        label="Name"
                        :error="holidayForm.errors.name"
                    >
                        <input
                            id="holiday-name"
                            v-model="holidayForm.name"
                            type="text"
                            required
                            placeholder="For example, Independence Day"
                            class="portal-input"
                        />
                    </AdminField>
                    <SiteButton
                        type="submit"
                        :disabled="holidayForm.processing"
                    >
                        Add holiday
                    </SiteButton>
                </form>

                <ul
                    v-if="holidays.length"
                    class="mt-6 divide-y divide-border border-y border-border"
                >
                    <li
                        v-for="holiday in holidays"
                        :key="holiday.id"
                        class="flex items-center gap-4 py-2.5"
                    >
                        <span class="w-36 tabular-nums">{{
                            holiday.label
                        }}</span>
                        <span class="font-semibold">{{ holiday.name }}</span>
                        <button
                            type="button"
                            class="ml-auto font-semibold text-destructive hover:underline"
                            @click="removeHoliday(holiday)"
                        >
                            Remove
                        </button>
                    </li>
                </ul>
                <p v-else class="mt-6 text-muted-foreground">
                    No public holidays added yet.
                </p>
            </PortalPanel>
        </div>
    </div>
</template>
