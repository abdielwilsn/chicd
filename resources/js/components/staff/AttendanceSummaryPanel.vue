<script setup lang="ts">
import { CalendarCheck2 } from '@lucide/vue';
import { computed } from 'vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import type { AttendanceSummary } from '@/types';

type Props = {
    attendance: AttendanceSummary;
    /** Start time and grace period, e.g. "08:00 with 15 minutes' grace". */
    startTimeLabel?: string;
};

const props = defineProps<Props>();

const counts = computed(() => [
    { label: 'Working days', value: props.attendance.workingDays },
    { label: 'Late', value: props.attendance.lateDays.length },
    { label: 'Absent', value: props.attendance.absentDays.length },
    { label: 'Leave', value: props.attendance.leaveDays.length },
]);
</script>

<!--
    Lateness, absence, leave and public holidays for a month. Used on the
    staff member's own attendance page and on the admin timesheet review.
-->
<template>
    <PortalPanel title="Lateness, absence and leave" :icon="CalendarCheck2">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-4">
            <div v-for="count in counts" :key="count.label">
                <dt
                    class="font-display text-[0.85rem] font-bold tracking-[0.12em] text-muted-foreground uppercase"
                >
                    {{ count.label }}
                </dt>
                <dd class="font-heading text-2xl font-bold tabular-nums">
                    {{ count.value }}
                </dd>
            </div>
        </dl>

        <div class="mt-6 grid gap-5 text-[0.95rem] sm:grid-cols-2">
            <div v-if="attendance.lateDays.length">
                <h3 class="font-semibold">Late starts</h3>
                <ul class="mt-1 space-y-0.5 text-muted-foreground">
                    <li v-for="day in attendance.lateDays" :key="day.date">
                        {{ day.label }}: started {{ day.startedAt }}
                    </li>
                </ul>
            </div>
            <div v-if="attendance.absentDays.length">
                <h3 class="font-semibold">Absent (no record, no leave)</h3>
                <ul class="mt-1 space-y-0.5 text-muted-foreground">
                    <li v-for="day in attendance.absentDays" :key="day.date">
                        {{ day.label }}
                    </li>
                </ul>
            </div>
            <div v-if="attendance.leaveDays.length">
                <h3 class="font-semibold">On leave</h3>
                <ul class="mt-1 space-y-0.5 text-muted-foreground">
                    <li v-for="day in attendance.leaveDays" :key="day.date">
                        {{ day.label }}: {{ day.type }}
                    </li>
                </ul>
            </div>
            <div v-if="attendance.holidays.length">
                <h3 class="font-semibold">Public holidays</h3>
                <ul class="mt-1 space-y-0.5 text-muted-foreground">
                    <li v-for="day in attendance.holidays" :key="day.date">
                        {{ day.label }}: {{ day.name }}
                    </li>
                </ul>
            </div>
        </div>

        <p
            v-if="startTimeLabel"
            class="mt-5 text-[0.9rem] text-muted-foreground"
        >
            Work starting after {{ startTimeLabel }} counts as late. A past
            working day with no record and no approved leave counts as absent.
        </p>
    </PortalPanel>
</template>
