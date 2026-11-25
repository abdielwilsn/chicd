<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    ChevronLeft,
    ChevronRight,
    MessageSquareWarning,
    Route,
    TriangleAlert,
    Users,
} from '@lucide/vue';
import SiteButton from '@/components/site/SiteButton.vue';
import DownloadLinks from '@/components/staff/DownloadLinks.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { formatMinutes } from '@/lib/duration';
import { index } from '@/routes/admin/attendance';
import { index as corrections } from '@/routes/admin/corrections';
import { consolidated, show } from '@/routes/admin/timesheets';
import type {
    AttendanceCounts,
    TimesheetStatusValue,
    TimesheetTotals,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Staff attendance', href: index() }],
    },
});

defineProps<{
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string | null;
    staff: {
        id: number;
        name: string;
        email: string;
        role: string;
        totals: TimesheetTotals;
        status: TimesheetStatusValue;
        statusLabel: string;
        pendingCorrections: number;
        attendance: AttendanceCounts;
    }[];
    workingNow: {
        key: string;
        staffId: number;
        name: string;
        mode: 'Office' | 'Field';
        since: string;
        where: string;
        flagged: boolean;
    }[];
    pendingCorrectionsTotal: number;
}>();
</script>

<template>
    <Head title="Staff attendance" />

    <div>
        <PortalPageHeader eyebrow="Admin" title="Staff attendance">
            <p>Everyone's office and field time in {{ monthLabel }}.</p>

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
                        :href="
                            nextMonth
                                ? index.url({ query: { month: nextMonth } })
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

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <Link
                v-if="pendingCorrectionsTotal"
                :href="corrections()"
                class="flex items-center gap-3 border-l-4 border-sun-400 bg-sun-50 px-5 py-4 font-semibold hover:bg-sun-100 dark:bg-sun-700/20"
            >
                <MessageSquareWarning class="size-5 shrink-0 text-sun-700" />
                {{ pendingCorrectionsTotal }}
                {{
                    pendingCorrectionsTotal === 1
                        ? 'correction request is'
                        : 'correction requests are'
                }}
                waiting for a decision.
            </Link>

            <PortalPanel title="Working now" :icon="Users">
                <ul
                    v-if="workingNow.length"
                    class="divide-y divide-border border-y border-border"
                >
                    <li
                        v-for="row in workingNow"
                        :key="row.key"
                        class="flex flex-wrap items-center gap-x-4 gap-y-1 py-3"
                    >
                        <span
                            class="flex w-20 items-center gap-1.5 font-display text-[0.85rem] font-bold tracking-[0.12em] uppercase"
                            :class="
                                row.mode === 'Office'
                                    ? 'text-sun-700 dark:text-sun-300'
                                    : 'text-navy-700 dark:text-navy-100'
                            "
                        >
                            <Building2
                                v-if="row.mode === 'Office'"
                                class="size-3.5"
                            />
                            <Route v-else class="size-3.5" />
                            {{ row.mode }}
                        </span>
                        <span class="font-heading font-bold">{{
                            row.name
                        }}</span>
                        <span class="text-muted-foreground">{{
                            row.where
                        }}</span>
                        <span
                            v-if="row.flagged"
                            class="flex items-center gap-1 text-[0.9rem] text-sun-700 dark:text-sun-300"
                        >
                            <TriangleAlert class="size-4" />
                            Outside office area
                        </span>
                        <span class="ml-auto text-muted-foreground tabular-nums"
                            >since {{ row.since }}</span
                        >
                    </li>
                </ul>
                <p v-else class="text-muted-foreground">
                    No one is clocked in or on a field activity right now.
                </p>
            </PortalPanel>

            <section class="space-y-4">
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <h2 class="font-heading text-2xl font-bold">
                            Timesheets for {{ monthLabel }}
                        </h2>
                        <p class="text-muted-foreground">
                            Office and field time are shown separately. Open
                            records are not counted.
                        </p>
                    </div>
                    <div>
                        <p
                            class="mb-1.5 font-display text-[0.85rem] font-bold tracking-[0.12em] text-muted-foreground uppercase"
                        >
                            Consolidated timesheet
                        </p>
                        <DownloadLinks
                            :pdf-url="
                                consolidated.url({ month, format: 'pdf' })
                            "
                            :csv-url="
                                consolidated.url({ month, format: 'csv' })
                            "
                        />
                    </div>
                </div>

                <div
                    v-if="staff.length"
                    class="overflow-x-auto border-y border-border bg-card"
                >
                    <table class="w-full min-w-[60rem] text-left">
                        <thead
                            class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Staff member</th>
                                <th class="px-4 py-3 text-right">Office</th>
                                <th class="px-4 py-3 text-right">Field</th>
                                <th class="px-4 py-3 text-right">Days</th>
                                <th class="px-4 py-3 text-right">
                                    Late · Absent · Leave
                                </th>
                                <th class="px-4 py-3">Needs attention</th>
                                <th class="px-4 py-3">Timesheet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="person in staff" :key="person.id">
                                <td class="px-4 py-3">
                                    <Link
                                        :href="show({ user: person.id, month })"
                                        class="font-heading font-bold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                                    >
                                        {{ person.name }}
                                    </Link>
                                    <span
                                        class="block text-[0.9rem] text-muted-foreground"
                                        >{{ person.role }} ·
                                        {{ person.email }}</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{
                                        formatMinutes(
                                            person.totals.officeMinutes,
                                        )
                                    }}
                                    <span
                                        class="block text-[0.85rem] text-muted-foreground"
                                        >{{
                                            person.totals.officeDays
                                        }}
                                        days</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{
                                        formatMinutes(
                                            person.totals.fieldMinutes,
                                        )
                                    }}
                                    <span
                                        class="block text-[0.85rem] text-muted-foreground"
                                        >{{
                                            person.totals.fieldDays
                                        }}
                                        days</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ person.totals.daysWorked }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    <span
                                        :class="{
                                            'font-semibold text-sun-700 dark:text-sun-300':
                                                person.attendance.lateDays,
                                        }"
                                        >{{ person.attendance.lateDays }}</span
                                    >
                                    ·
                                    <span
                                        :class="{
                                            'font-semibold text-terracotta-600':
                                                person.attendance.absentDays,
                                        }"
                                        >{{
                                            person.attendance.absentDays
                                        }}</span
                                    >
                                    · {{ person.attendance.leaveDays }}
                                    <span
                                        class="block text-[0.85rem] text-muted-foreground"
                                        >of
                                        {{ person.attendance.workingDays }}
                                        working days</span
                                    >
                                </td>
                                <td class="space-y-0.5 px-4 py-3 text-[0.9rem]">
                                    <p
                                        v-if="person.totals.flaggedClockings"
                                        class="text-sun-700 dark:text-sun-300"
                                    >
                                        {{ person.totals.flaggedClockings }}
                                        outside office area
                                    </p>
                                    <p v-if="person.totals.openRecords">
                                        {{ person.totals.openRecords }} still
                                        open
                                    </p>
                                    <p v-if="person.pendingCorrections">
                                        {{ person.pendingCorrections }}
                                        correction
                                        {{
                                            person.pendingCorrections === 1
                                                ? 'request'
                                                : 'requests'
                                        }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadge
                                        :status="person.status"
                                        :label="person.statusLabel"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <PortalEmptyState
                    v-else
                    :icon="Users"
                    title="No staff accounts yet"
                />
            </section>
        </div>
    </div>
</template>
