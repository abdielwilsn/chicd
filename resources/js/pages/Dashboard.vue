<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Building2, CalendarCheck, Route } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import SiteButton from '@/components/site/SiteButton.vue';
import AttendanceRecordList from '@/components/staff/AttendanceRecordList.vue';
import FieldActivityPanel from '@/components/staff/FieldActivityPanel.vue';
import OfficeAttendancePanel from '@/components/staff/OfficeAttendancePanel.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as myAttendance } from '@/routes/attendance';
import type { FieldActivity, OfficeAttendance, WorkMode } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Today',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    today: string;
    openOfficeAttendance: OfficeAttendance | null;
    openFieldActivity: FieldActivity | null;
    officeAttendances: OfficeAttendance[];
    fieldActivities: FieldActivity[];
    programmes: string[];
}>();

const mode = ref<WorkMode>(props.openFieldActivity ? 'field' : 'office');

const modes = computed<
    {
        value: WorkMode;
        label: string;
        hint: string;
        icon: LucideIcon;
        status: string | null;
    }[]
>(() => [
    {
        value: 'office',
        label: 'Office',
        hint: 'Clock in and out',
        icon: Building2,
        status: props.openOfficeAttendance ? 'Clocked in' : null,
    },
    {
        value: 'field',
        label: 'Field / Activity',
        hint: 'Record field work',
        icon: Route,
        status: props.openFieldActivity ? 'In progress' : null,
    },
]);
</script>

<template>
    <Head title="Today" />

    <div>
        <PortalPageHeader title="Today">
            <p>{{ today }}</p>
        </PortalPageHeader>

        <div
            class="portal-container grid gap-10 py-8 sm:py-10 lg:grid-cols-12 lg:gap-12"
        >
            <div class="space-y-6 lg:col-span-7">
                <div role="radiogroup" aria-label="Work mode">
                    <p class="site-label mb-4 dark:text-brand-300">
                        Choose your work mode
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="option in modes"
                            :key="option.value"
                            type="button"
                            role="radio"
                            :aria-checked="mode === option.value"
                            :class="
                                cn(
                                    'relative flex flex-col items-start gap-1 rounded-[3px] border-2 px-4 py-4 text-left transition-colors focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 focus-visible:outline-none sm:px-5',
                                    mode === option.value
                                        ? 'border-brand-700 bg-brand-700 text-white shadow-[inset_0_-5px_0_var(--color-sun-400)]'
                                        : 'border-border bg-card text-foreground hover:border-brand-600',
                                )
                            "
                            @click="mode = option.value"
                        >
                            <span class="flex items-center gap-2">
                                <component
                                    :is="option.icon"
                                    class="size-5"
                                    :class="
                                        mode === option.value
                                            ? 'text-sun-400'
                                            : 'text-brand-600 dark:text-brand-300'
                                    "
                                />
                                <span
                                    class="font-display text-lg leading-none font-bold tracking-[0.04em] uppercase sm:text-xl"
                                    >{{ option.label }}</span
                                >
                            </span>
                            <span
                                class="text-[0.92rem]"
                                :class="
                                    mode === option.value
                                        ? 'text-brand-100'
                                        : 'text-muted-foreground'
                                "
                                >{{ option.status ?? option.hint }}</span
                            >
                            <span
                                v-if="option.status"
                                class="absolute top-3 right-3 size-2.5 rounded-full bg-sun-400"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                </div>

                <OfficeAttendancePanel
                    v-if="mode === 'office'"
                    :open-attendance="openOfficeAttendance"
                />
                <FieldActivityPanel
                    v-else
                    :open-activity="openFieldActivity"
                    :programmes="programmes"
                />
            </div>

            <section class="space-y-4 lg:col-span-5">
                <h2 class="site-label dark:text-brand-300">Recorded today</h2>

                <AttendanceRecordList
                    v-if="officeAttendances.length || fieldActivities.length"
                    :office-attendances="officeAttendances"
                    :field-activities="fieldActivities"
                />
                <PortalEmptyState
                    v-else
                    :icon="CalendarCheck"
                    title="Nothing recorded yet today"
                >
                    Your office sessions and field activities will appear here
                    as you record them.
                </PortalEmptyState>

                <SiteButton
                    :href="myAttendance.url()"
                    variant="link"
                    class="dark:text-brand-300 dark:hover:text-brand-100"
                >
                    See this month's attendance
                </SiteButton>
            </section>
        </div>
    </div>
</template>
