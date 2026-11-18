<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarX2, ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import SiteButton from '@/components/site/SiteButton.vue';
import AttendanceRecordList from '@/components/staff/AttendanceRecordList.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import { formatMinutes } from '@/lib/duration';
import { index } from '@/routes/attendance';
import type { FieldActivity, OfficeAttendance } from '@/types';

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
}>();

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

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <section v-for="day in days" :key="day.date" class="space-y-3">
                <h2 class="font-heading text-xl font-bold">
                    {{ day.label }}
                </h2>
                <AttendanceRecordList
                    :office-attendances="day.officeAttendances"
                    :field-activities="day.fieldActivities"
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
    </div>
</template>
