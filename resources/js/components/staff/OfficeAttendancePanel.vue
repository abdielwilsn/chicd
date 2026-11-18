<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Building2, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import LocationNote from '@/components/staff/LocationNote.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import { Spinner } from '@/components/ui/spinner';
import { useDeviceLocation } from '@/composables/useDeviceLocation';
import { store, update } from '@/routes/office-attendances';
import type { OfficeAttendance } from '@/types';

const props = defineProps<{
    openAttendance: OfficeAttendance | null;
}>();

const { locating, locationError, readLocation } = useDeviceLocation();

const form = useForm({
    latitude: null as number | null,
    longitude: null as number | null,
    accuracy: null as number | null,
});

const busy = computed(() => locating.value || form.processing);

/**
 * Errors from the server, including the "already clocked in/out" error,
 * which is not tied to a form field.
 */
const errors = computed(
    () =>
        form.errors as Partial<
            Record<'attendance' | 'latitude' | 'longitude', string>
        >,
);

const errorMessage = computed(
    () =>
        locationError.value ??
        errors.value.attendance ??
        errors.value.latitude ??
        errors.value.longitude,
);

async function submit(): Promise<void> {
    form.clearErrors();

    const location = await readLocation();

    if (!location) {
        return;
    }

    form.latitude = location.latitude;
    form.longitude = location.longitude;
    form.accuracy = location.accuracy;

    const options = { preserveScroll: true, onSuccess: () => form.reset() };

    if (props.openAttendance) {
        form.patch(update.url(props.openAttendance.id), options);
    } else {
        form.post(store.url(), options);
    }
}
</script>

<template>
    <PortalPanel title="Office attendance" :icon="Building2">
        <template #description>
            Clock in when you arrive at the office and clock out when you leave.
        </template>

        <div class="space-y-6">
            <div
                v-if="openAttendance"
                class="border-l-4 border-brand-600 bg-brand-50 px-5 py-4 dark:bg-brand-950/60"
            >
                <p
                    class="font-display text-[0.9rem] font-bold tracking-[0.12em] text-brand-800 uppercase dark:text-brand-200"
                >
                    Clocked in
                </p>
                <p class="mt-1 flex flex-wrap items-baseline gap-x-3">
                    <span
                        class="font-display text-5xl leading-none font-extrabold text-brand-700 tabular-nums dark:text-brand-200"
                        >{{ openAttendance.clocked_in_time }}</span
                    >
                    <span class="text-muted-foreground">
                        {{ openAttendance.date }}
                        <template v-if="openAttendance.office">
                            · {{ openAttendance.office }}
                        </template>
                    </span>
                </p>
                <p
                    v-if="!openAttendance.clocked_in_within_geofence"
                    class="mt-3 flex items-start gap-2 border-t border-sun-300/60 pt-3 text-[0.95rem] text-sun-700 dark:text-sun-300"
                >
                    <TriangleAlert class="mt-0.5 size-4 shrink-0" />
                    Your location was outside the office area, so this clock-in
                    has been flagged for review.
                </p>
            </div>
            <div
                v-else
                class="border-l-4 border-border bg-secondary px-5 py-4 text-secondary-foreground"
            >
                <p
                    class="font-display text-[0.9rem] font-bold tracking-[0.12em] uppercase"
                >
                    Not clocked in
                </p>
                <p class="mt-1 text-muted-foreground">
                    Press Clock In when you arrive at the office.
                </p>
            </div>

            <div>
                <SiteButton
                    size="lg"
                    :variant="openAttendance ? 'donate' : 'primary'"
                    class="w-full sm:w-auto sm:min-w-56"
                    :disabled="busy"
                    data-test="office-clock-button"
                    @click="submit"
                >
                    <Spinner v-if="busy" />
                    <template v-if="locating">Finding your location…</template>
                    <template v-else>
                        {{ openAttendance ? 'Clock Out' : 'Clock In' }}
                    </template>
                </SiteButton>

                <InputError :message="errorMessage" class="mt-3" />
            </div>

            <LocationNote>
                Your location is read once, when you press the button. It is not
                tracked at any other time.
            </LocationNote>
        </div>
    </PortalPanel>
</template>
