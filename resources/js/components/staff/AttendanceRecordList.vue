<script setup lang="ts">
import {
    Building2,
    MessageSquareWarning,
    Route,
    TriangleAlert,
} from '@lucide/vue';
import { formatMinutes } from '@/lib/duration';
import type {
    CorrectableRecord,
    FieldActivity,
    OfficeAttendance,
} from '@/types';

withDefaults(
    defineProps<{
        officeAttendances: OfficeAttendance[];
        fieldActivities: FieldActivity[];
        /** Show a "Request a correction" link on each record. */
        correctable?: boolean;
    }>(),
    { correctable: false },
);

const emit = defineEmits<{
    requestCorrection: [record: CorrectableRecord];
}>();

function officeSummary(attendance: OfficeAttendance): string {
    return `Office session on ${attendance.date}, ${attendance.clocked_in_time} to ${attendance.clocked_out_time ?? 'no clock-out'}`;
}

function fieldSummary(activity: FieldActivity): string {
    return `${activity.activity} on ${activity.date}, ${activity.started_time} to ${activity.ended_time ?? 'not ended'}`;
}

/**
 * Describes which ends of an office session fell outside the geofence.
 */
function geofenceFlag(attendance: OfficeAttendance): string | null {
    const inOutside = !attendance.clocked_in_within_geofence;
    const outOutside = attendance.clocked_out_within_geofence === false;

    if (inOutside && outOutside) {
        return 'Clock-in and clock-out outside the office area';
    }

    if (inOutside) {
        return 'Clock-in outside the office area';
    }

    return outOutside ? 'Clock-out outside the office area' : null;
}
</script>

<template>
    <ul class="divide-y divide-border border-y border-border bg-card">
        <li
            v-for="attendance in officeAttendances"
            :key="`office-${attendance.id}`"
            class="grid grid-cols-[4.25rem_minmax(0,1fr)_auto] gap-x-3 px-4 py-4 sm:gap-x-4 sm:px-5"
        >
            <p
                class="font-display text-lg leading-tight font-bold text-brand-700 tabular-nums dark:text-brand-200"
            >
                {{ attendance.clocked_in_time }}
                <span class="block text-muted-foreground">
                    {{ attendance.clocked_out_time ?? 'now' }}
                </span>
            </p>
            <div class="min-w-0">
                <p
                    class="flex items-center gap-1.5 font-display text-[0.85rem] font-bold tracking-[0.12em] text-sun-700 uppercase dark:text-sun-300"
                >
                    <Building2 class="size-3.5" />
                    Office
                </p>
                <p class="mt-0.5 font-heading text-[1.05rem] font-bold">
                    {{ attendance.office ?? 'Office attendance' }}
                </p>
                <p
                    v-if="!attendance.clocked_out_time"
                    class="text-[0.95rem] text-muted-foreground"
                >
                    Still clocked in
                </p>
                <p
                    v-if="geofenceFlag(attendance)"
                    class="mt-1.5 flex items-center gap-1.5 text-[0.9rem] text-sun-700 dark:text-sun-300"
                >
                    <TriangleAlert class="size-4 shrink-0" />
                    {{ geofenceFlag(attendance) }}
                </p>
                <button
                    v-if="correctable"
                    type="button"
                    class="mt-2 inline-flex items-center gap-1.5 text-[0.9rem] font-semibold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                    @click="
                        emit('requestCorrection', {
                            type: 'office_attendance',
                            id: attendance.id,
                            summary: officeSummary(attendance),
                        })
                    "
                >
                    <MessageSquareWarning class="size-4" />
                    Request a correction
                </button>
            </div>
            <p
                class="font-display text-lg font-bold text-foreground tabular-nums"
            >
                {{
                    attendance.minutes !== null
                        ? formatMinutes(attendance.minutes)
                        : ''
                }}
            </p>
        </li>

        <li
            v-for="activity in fieldActivities"
            :key="`field-${activity.id}`"
            class="grid grid-cols-[4.25rem_minmax(0,1fr)_auto] gap-x-3 px-4 py-4 sm:gap-x-4 sm:px-5"
        >
            <p
                class="font-display text-lg leading-tight font-bold text-brand-700 tabular-nums dark:text-brand-200"
            >
                {{ activity.started_time }}
                <span class="block text-muted-foreground">
                    {{ activity.ended_time ?? 'now' }}
                </span>
            </p>
            <div class="min-w-0">
                <p
                    class="flex items-center gap-1.5 font-display text-[0.85rem] font-bold tracking-[0.12em] text-lagoon-700 uppercase dark:text-lagoon-100"
                >
                    <Route class="size-3.5" />
                    Field
                </p>
                <p class="mt-0.5 font-heading text-[1.05rem] font-bold">
                    {{ activity.activity }}
                </p>
                <p class="text-[0.95rem] text-muted-foreground">
                    {{ activity.programme }} · {{ activity.location }}
                    <template v-if="!activity.ended_time">
                        · In progress
                    </template>
                </p>
                <button
                    v-if="correctable"
                    type="button"
                    class="mt-2 inline-flex items-center gap-1.5 text-[0.9rem] font-semibold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                    @click="
                        emit('requestCorrection', {
                            type: 'field_activity',
                            id: activity.id,
                            summary: fieldSummary(activity),
                        })
                    "
                >
                    <MessageSquareWarning class="size-4" />
                    Request a correction
                </button>
            </div>
            <p
                class="font-display text-lg font-bold text-foreground tabular-nums"
            >
                {{
                    activity.minutes !== null
                        ? formatMinutes(activity.minutes)
                        : ''
                }}
            </p>
        </li>
    </ul>
</template>
