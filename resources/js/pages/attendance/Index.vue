<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CalendarPlus,
    CalendarX2,
    ChevronLeft,
    ChevronRight,
    MessageSquareWarning,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import SiteButton from '@/components/site/SiteButton.vue';
import AttendanceRecordList from '@/components/staff/AttendanceRecordList.vue';
import CorrectionRequestDialog from '@/components/staff/CorrectionRequestDialog.vue';
import DownloadLinks from '@/components/staff/DownloadLinks.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { formatMinutes } from '@/lib/duration';
import { index } from '@/routes/attendance';
import { download } from '@/routes/attendance/timesheet';
import AttendanceSummaryPanel from '@/components/staff/AttendanceSummaryPanel.vue';
import type {
    AttendanceSummary,
    CorrectableRecord,
    CorrectionRequest,
    FieldActivity,
    OfficeAttendance,
    TimesheetStatusValue,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'My attendance',
                href: index(),
            },
        ],
    },
});

const props = defineProps<{
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string | null;
    days: {
        date: string;
        label: string;
        officeAttendances: OfficeAttendance[];
        fieldActivities: FieldActivity[];
    }[];
    totals: {
        officeMinutes: number;
        fieldMinutes: number;
        officeDays: number;
        fieldDays: number;
    };
    attendance: AttendanceSummary;
    lateAfter: string;
    timesheet: {
        status: TimesheetStatusValue;
        label: string;
        approvedAt: string | null;
        approvedBy: string | null;
    };
    correctionRequests: CorrectionRequest[];
    today: string;
}>();

const correctionOpen = ref(false);
const correctionRecord = ref<CorrectableRecord | null>(null);

function requestCorrection(record: CorrectableRecord | null): void {
    correctionRecord.value = record;
    correctionOpen.value = true;
}

const isApproved = computed(() => props.timesheet.status === 'approved');

const figures = computed(() => [
    {
        label: 'Office time',
        value: formatMinutes(props.totals.officeMinutes),
        detail: `${props.totals.officeDays} ${props.totals.officeDays === 1 ? 'day' : 'days'} in the office`,
    },
    {
        label: 'Field time',
        value: formatMinutes(props.totals.fieldMinutes),
        detail: `${props.totals.fieldDays} ${props.totals.fieldDays === 1 ? 'day' : 'days'} with field activities`,
    },
]);
</script>

<template>
    <Head title="My attendance" />

    <div>
        <PortalPageHeader title="My attendance">
            <p>
                Your office sessions and field activities in {{ monthLabel }}.
            </p>

            <template #actions>
                <nav
                    aria-label="Choose a month"
                    class="flex items-center gap-2"
                >
                    <SiteButton
                        :href="index.url({ query: { month: previousMonth } })"
                        variant="ghostLight"
                        size="sm"
                        class="px-3"
                    >
                        <ChevronLeft />
                        <span class="sr-only">Previous month</span>
                    </SiteButton>
                    <span
                        class="min-w-36 text-center font-display text-xl font-bold tracking-[0.04em] uppercase"
                        >{{ monthLabel }}</span
                    >
                    <SiteButton
                        v-if="nextMonth"
                        :href="index.url({ query: { month: nextMonth } })"
                        variant="ghostLight"
                        size="sm"
                        class="px-3"
                    >
                        <ChevronRight />
                        <span class="sr-only">Next month</span>
                    </SiteButton>
                    <SiteButton
                        v-else
                        variant="ghostLight"
                        size="sm"
                        class="px-3"
                        disabled
                    >
                        <ChevronRight />
                        <span class="sr-only">Next month</span>
                    </SiteButton>
                </nav>
            </template>
        </PortalPageHeader>

        <section class="border-b border-border bg-card">
            <div class="portal-container py-8 sm:py-10">
                <h2 class="site-label dark:text-brand-300">
                    {{ monthLabel }} at a glance
                </h2>
                <dl
                    class="mt-6 grid gap-y-8 sm:grid-cols-2 sm:divide-x sm:divide-border"
                >
                    <div
                        v-for="(figure, position) in figures"
                        :key="figure.label"
                        class="pr-6"
                        :class="{ 'sm:pl-8': position > 0 }"
                    >
                        <dt
                            class="font-display text-lg font-bold tracking-[0.06em] text-sun-600 uppercase"
                        >
                            {{ figure.label }}
                        </dt>
                        <dd
                            class="mt-1 font-display text-5xl leading-none font-extrabold text-brand-700 tabular-nums sm:text-6xl dark:text-brand-200"
                        >
                            {{ figure.value }}
                        </dd>
                        <dd class="mt-2 text-muted-foreground">
                            {{ figure.detail }}
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="border-b border-border bg-secondary/60">
            <div
                class="portal-container flex flex-col gap-4 py-6 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="flex flex-wrap items-center gap-3 font-heading text-xl font-bold"
                    >
                        {{ monthLabel }} timesheet
                        <StatusBadge
                            :status="timesheet.status"
                            :label="timesheet.label"
                        />
                    </h2>
                    <p class="mt-1 text-muted-foreground">
                        <template v-if="isApproved">
                            Approved by
                            {{ timesheet.approvedBy ?? 'an admin' }} on
                            {{ timesheet.approvedAt }}.
                        </template>
                        <template v-else>
                            Your timesheet is built from what you record. An
                            admin approves it after the month ends.
                        </template>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <DownloadLinks
                        v-if="isApproved"
                        :pdf-url="download.url({ month, format: 'pdf' })"
                        :csv-url="download.url({ month, format: 'csv' })"
                    />
                    <SiteButton
                        variant="outline"
                        size="sm"
                        data-test="report-missing-record"
                        @click="requestCorrection(null)"
                    >
                        <CalendarPlus />
                        Report a missing record
                    </SiteButton>
                </div>
            </div>
        </section>

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <AttendanceSummaryPanel
                :attendance="attendance"
                :start-time-label="lateAfter"
            />

            <section v-if="correctionRequests.length" class="space-y-3">
                <h2
                    class="flex items-center gap-2 font-heading text-xl font-bold"
                >
                    <MessageSquareWarning
                        class="size-5 text-brand-600 dark:text-brand-300"
                    />
                    Your correction requests
                </h2>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <li
                        v-for="request in correctionRequests"
                        :key="request.id"
                        class="space-y-1.5 px-4 py-4 sm:px-5"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <StatusBadge
                                :status="request.status"
                                :label="request.status_label"
                            />
                            <span class="font-heading font-bold">{{
                                request.work_date_label
                            }}</span>
                            <span class="text-muted-foreground"
                                >· {{ request.record }}</span
                            >
                        </div>
                        <p class="leading-relaxed">{{ request.details }}</p>
                        <p
                            v-if="request.resolution_note"
                            class="border-l-4 border-brand-600 bg-brand-50 px-3 py-2 text-[0.95rem] dark:bg-brand-950/60"
                        >
                            <span class="font-semibold"
                                >{{ request.resolved_by ?? 'Admin' }}:</span
                            >
                            {{ request.resolution_note }}
                        </p>
                    </li>
                </ul>
            </section>

            <section v-for="day in days" :key="day.date" class="space-y-3">
                <h2 class="font-heading text-xl font-bold">
                    {{ day.label }}
                </h2>
                <AttendanceRecordList
                    :office-attendances="day.officeAttendances"
                    :field-activities="day.fieldActivities"
                    correctable
                    @request-correction="requestCorrection"
                />
            </section>

            <PortalEmptyState
                v-if="!days.length"
                :icon="CalendarX2"
                :title="`Nothing recorded in ${monthLabel}`"
            >
                Office sessions and field activities you record will be listed
                here by day.
            </PortalEmptyState>
        </div>

        <CorrectionRequestDialog
            v-model:open="correctionOpen"
            :record="correctionRecord"
            :max-date="today"
        />
    </div>
</template>
