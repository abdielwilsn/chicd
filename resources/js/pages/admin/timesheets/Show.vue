<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Building2,
    CalendarX2,
    ChevronLeft,
    ChevronRight,
    History,
    MessageSquareWarning,
    Pencil,
    Route,
    TriangleAlert,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import CapturedLocation from '@/components/staff/CapturedLocation.vue';
import CorrectionRequestCard from '@/components/staff/CorrectionRequestCard.vue';
import DownloadLinks from '@/components/staff/DownloadLinks.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import RecordEditDialog from '@/components/staff/RecordEditDialog.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { Spinner } from '@/components/ui/spinner';
import { formatMinutes } from '@/lib/duration';
import { index as attendanceOverview } from '@/routes/admin/attendance';
import { approve, download, reopen, show } from '@/routes/admin/timesheets';
import AttendanceSummaryPanel from '@/components/staff/AttendanceSummaryPanel.vue';
import type {
    AttendanceSummary,
    AdminFieldActivity,
    AdminOfficeAttendance,
    CorrectionRequest,
    RecordChange,
    TimesheetStatusValue,
    TimesheetTotals,
} from '@/types';

const props = defineProps<{
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string | null;
    staff: { id: number; name: string; email: string };
    timesheet: {
        status: TimesheetStatusValue;
        label: string;
        approvedAt: string | null;
        approvedBy: string | null;
        approvalBlockers: string[];
    };
    totals: TimesheetTotals;
    attendance: AttendanceSummary;
    lateAfter: string;
    days: {
        date: string;
        label: string;
        officeAttendances: AdminOfficeAttendance[];
        fieldActivities: AdminFieldActivity[];
    }[];
    correctionRequests: CorrectionRequest[];
    history: RecordChange[];
    programmes: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Staff attendance', href: attendanceOverview() },
            { title: 'Timesheet', href: '#' },
        ],
    },
});

const isApproved = computed(() => props.timesheet.status === 'approved');
const pendingRequests = computed(() =>
    props.correctionRequests.filter((request) => request.status === 'pending'),
);

const editOpen = ref(false);
const editing = ref<
    | { type: 'office_attendance'; data: AdminOfficeAttendance }
    | { type: 'field_activity'; data: AdminFieldActivity }
    | null
>(null);

function edit(
    record:
        | { type: 'office_attendance'; data: AdminOfficeAttendance }
        | { type: 'field_activity'; data: AdminFieldActivity },
): void {
    editing.value = record;
    editOpen.value = true;
}

const approveForm = useForm({});
const reopenForm = useForm({ reason: '' });
const reopening = ref(false);

const approvalErrors = computed(() => {
    const error = (approveForm.errors as Partial<Record<'approval', string>>)
        .approval;

    return error ? [error] : [];
});

function approveTimesheet(): void {
    approveForm.post(
        approve.url({ user: props.staff.id, month: props.month }),
        { preserveScroll: true },
    );
}

function reopenTimesheet(): void {
    reopenForm.delete(
        reopen.url({ user: props.staff.id, month: props.month }),
        {
            preserveScroll: true,
            onSuccess: () => {
                reopening.value = false;
                reopenForm.reset();
            },
        },
    );
}

const figures = computed(() => [
    {
        label: 'Office time',
        value: formatMinutes(props.totals.officeMinutes),
        detail: `${props.totals.officeDays} days`,
    },
    {
        label: 'Field time',
        value: formatMinutes(props.totals.fieldMinutes),
        detail: `${props.totals.fieldDays} days`,
    },
    {
        label: 'Days worked',
        value: String(props.totals.daysWorked),
        detail: `${props.totals.flaggedClockings} outside office area`,
    },
]);
</script>

<template>
    <Head :title="`${staff.name} · ${monthLabel}`" />

    <div>
        <PortalPageHeader eyebrow="Admin · Timesheet" :title="staff.name">
            <p>{{ staff.email }} · {{ monthLabel }}</p>

            <template #actions>
                <nav
                    aria-label="Choose a month"
                    class="flex items-center gap-2"
                >
                    <SiteButton
                        :href="
                            show.url({ user: staff.id, month: previousMonth })
                        "
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
                        :href="
                            nextMonth
                                ? show.url({ user: staff.id, month: nextMonth })
                                : undefined
                        "
                        :disabled="!nextMonth"
                        variant="ghostLight"
                        size="sm"
                        class="px-3"
                    >
                        <ChevronRight />
                        <span class="sr-only">Next month</span>
                    </SiteButton>
                </nav>
            </template>
        </PortalPageHeader>

        <section class="border-b border-border bg-card">
            <div class="portal-container py-8">
                <dl
                    class="grid gap-y-6 sm:grid-cols-3 sm:divide-x sm:divide-border"
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
                            class="mt-1 font-display text-5xl leading-none font-extrabold text-brand-700 tabular-nums dark:text-brand-200"
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

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <PortalPanel title="Approval">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <StatusBadge
                            :status="timesheet.status"
                            :label="timesheet.label"
                        />
                        <span v-if="isApproved" class="text-muted-foreground">
                            by {{ timesheet.approvedBy ?? 'an admin' }} on
                            {{ timesheet.approvedAt }}
                        </span>
                        <div class="ml-auto">
                            <DownloadLinks
                                :pdf-url="
                                    download.url({
                                        user: staff.id,
                                        month,
                                        format: 'pdf',
                                    })
                                "
                                :csv-url="
                                    download.url({
                                        user: staff.id,
                                        month,
                                        format: 'csv',
                                    })
                                "
                            />
                        </div>
                    </div>

                    <template v-if="!isApproved">
                        <ul
                            v-if="timesheet.approvalBlockers.length"
                            class="space-y-1"
                        >
                            <li
                                v-for="blocker in timesheet.approvalBlockers"
                                :key="blocker"
                                class="flex items-center gap-2 text-sun-700 dark:text-sun-300"
                            >
                                <TriangleAlert class="size-4 shrink-0" />
                                {{ blocker }}
                            </li>
                        </ul>
                        <SiteButton
                            :disabled="
                                approveForm.processing ||
                                timesheet.approvalBlockers.length > 0
                            "
                            data-test="approve-timesheet"
                            @click="approveTimesheet"
                        >
                            <Spinner v-if="approveForm.processing" />
                            Approve timesheet
                        </SiteButton>
                        <InputError
                            v-for="error in approvalErrors"
                            :key="error"
                            :message="error"
                        />
                    </template>

                    <template v-else>
                        <p class="text-muted-foreground">
                            The month's records are locked. Reopen the timesheet
                            to correct them.
                        </p>
                        <SiteButton
                            v-if="!reopening"
                            variant="outline"
                            size="sm"
                            @click="reopening = true"
                            >Reopen for corrections</SiteButton
                        >
                        <form
                            v-else
                            class="max-w-xl space-y-3"
                            @submit.prevent="reopenTimesheet"
                        >
                            <label for="reopen-reason" class="portal-label-text"
                                >Why is it being reopened?</label
                            >
                            <textarea
                                id="reopen-reason"
                                v-model="reopenForm.reason"
                                rows="2"
                                maxlength="1000"
                                class="portal-input"
                            />
                            <InputError :message="reopenForm.errors.reason" />
                            <div class="flex gap-2">
                                <SiteButton
                                    type="submit"
                                    size="sm"
                                    :disabled="reopenForm.processing"
                                    >Reopen</SiteButton
                                >
                                <SiteButton
                                    variant="link"
                                    size="sm"
                                    @click="reopening = false"
                                    >Cancel</SiteButton
                                >
                            </div>
                        </form>
                    </template>
                </div>
            </PortalPanel>

            <AttendanceSummaryPanel
                :attendance="attendance"
                :start-time-label="lateAfter"
            />

            <section v-if="correctionRequests.length" class="space-y-3">
                <h2
                    class="flex items-center gap-2 font-heading text-2xl font-bold"
                >
                    <MessageSquareWarning
                        class="size-6 text-brand-600 dark:text-brand-300"
                    />
                    Correction requests
                </h2>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <CorrectionRequestCard
                        v-for="request in correctionRequests"
                        :key="request.id"
                        :request="request"
                    />
                </ul>
            </section>

            <section v-for="day in days" :key="day.date" class="space-y-3">
                <h2 class="font-heading text-xl font-bold">{{ day.label }}</h2>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <li
                        v-for="attendance in day.officeAttendances"
                        :key="`office-${attendance.id}`"
                        class="space-y-2 px-4 py-4 sm:px-5"
                    >
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1"
                        >
                            <span
                                class="flex items-center gap-1.5 font-display text-[0.85rem] font-bold tracking-[0.12em] text-sun-700 uppercase dark:text-sun-300"
                            >
                                <Building2 class="size-3.5" />
                                Office
                            </span>
                            <span class="font-heading font-bold tabular-nums"
                                >{{ attendance.clocked_in_time }}–{{
                                    attendance.clocked_out_time ?? 'still open'
                                }}</span
                            >
                            <span class="text-muted-foreground">{{
                                attendance.office ?? 'No office on record'
                            }}</span>
                            <span
                                v-if="attendance.minutes !== null"
                                class="font-display text-lg font-bold tabular-nums"
                                >{{ formatMinutes(attendance.minutes) }}</span
                            >
                            <SiteButton
                                v-if="!isApproved"
                                variant="link"
                                class="ml-auto"
                                @click="
                                    edit({
                                        type: 'office_attendance',
                                        data: attendance,
                                    })
                                "
                            >
                                <Pencil />
                                Correct
                            </SiteButton>
                        </div>
                        <CapturedLocation
                            label="Clock-in"
                            :location="attendance.clock_in_location"
                            :within-geofence="
                                attendance.clocked_in_within_geofence
                            "
                        />
                        <CapturedLocation
                            label="Clock-out"
                            :location="attendance.clock_out_location"
                            :within-geofence="
                                attendance.clocked_out_within_geofence
                            "
                        />
                    </li>

                    <li
                        v-for="activity in day.fieldActivities"
                        :key="`field-${activity.id}`"
                        class="space-y-2 px-4 py-4 sm:px-5"
                    >
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1"
                        >
                            <span
                                class="flex items-center gap-1.5 font-display text-[0.85rem] font-bold tracking-[0.12em] text-navy-700 uppercase dark:text-navy-100"
                            >
                                <Route class="size-3.5" />
                                Field
                            </span>
                            <span class="font-heading font-bold tabular-nums"
                                >{{ activity.started_time }}–{{
                                    activity.ended_time ?? 'still open'
                                }}</span
                            >
                            <span class="font-semibold">{{
                                activity.activity
                            }}</span>
                            <span class="text-muted-foreground"
                                >{{ activity.programme }} ·
                                {{ activity.location }}</span
                            >
                            <span
                                v-if="activity.minutes !== null"
                                class="font-display text-lg font-bold tabular-nums"
                                >{{ formatMinutes(activity.minutes) }}</span
                            >
                            <SiteButton
                                v-if="!isApproved"
                                variant="link"
                                class="ml-auto"
                                @click="
                                    edit({
                                        type: 'field_activity',
                                        data: activity,
                                    })
                                "
                            >
                                <Pencil />
                                Correct
                            </SiteButton>
                        </div>
                        <p
                            v-if="activity.description"
                            class="text-[0.95rem] leading-relaxed"
                        >
                            {{ activity.description }}
                        </p>
                        <CapturedLocation
                            label="Start"
                            :location="activity.start_location"
                        />
                        <CapturedLocation
                            label="End"
                            :location="activity.end_location"
                        />
                    </li>
                </ul>
            </section>

            <PortalEmptyState
                v-if="!days.length"
                :icon="CalendarX2"
                :title="`Nothing recorded in ${monthLabel}`"
            />

            <section v-if="history.length" class="space-y-3">
                <h2
                    class="flex items-center gap-2 font-heading text-2xl font-bold"
                >
                    <History
                        class="size-6 text-brand-600 dark:text-brand-300"
                    />
                    Audit trail
                </h2>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <li
                        v-for="change in history"
                        :key="change.id"
                        class="space-y-1 px-4 py-3 sm:px-5"
                    >
                        <p class="flex flex-wrap gap-x-2">
                            <span class="font-heading font-bold">{{
                                change.action
                            }}</span>
                            <span>{{ change.record }}</span>
                            <span class="text-muted-foreground"
                                >by {{ change.changed_by }},
                                {{ change.changed_at }}</span
                            >
                        </p>
                        <ul
                            v-if="change.changes.length"
                            class="text-[0.95rem] tabular-nums"
                        >
                            <li
                                v-for="item in change.changes"
                                :key="item.field"
                            >
                                {{ item.field }}: {{ item.from }} →
                                {{ item.to }}
                            </li>
                        </ul>
                        <p class="text-[0.95rem] text-muted-foreground">
                            Reason: {{ change.reason }}
                        </p>
                    </li>
                </ul>
            </section>
        </div>

        <RecordEditDialog
            v-model:open="editOpen"
            :record="editing"
            :pending-requests="pendingRequests"
            :programmes="programmes"
        />
    </div>
</template>
