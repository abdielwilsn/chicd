<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Route } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import LocationNote from '@/components/staff/LocationNote.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import { Spinner } from '@/components/ui/spinner';
import { useDeviceLocation } from '@/composables/useDeviceLocation';
import { store, update } from '@/routes/field-activities';
import type { FieldActivity } from '@/types';

const props = defineProps<{
    openActivity: FieldActivity | null;
    programmes: string[];
}>();

const { locating, locationError, readLocation } = useDeviceLocation();

const startForm = useForm({
    programme: '',
    activity: '',
    description: '',
    location: '',
    latitude: null as number | null,
    longitude: null as number | null,
    accuracy: null as number | null,
});

const endForm = useForm({
    latitude: null as number | null,
    longitude: null as number | null,
    accuracy: null as number | null,
});

const busy = computed(
    () => locating.value || startForm.processing || endForm.processing,
);

const startLocationError = computed(
    () =>
        locationError.value ??
        (startForm.errors as Partial<Record<'field_activity', string>>)
            .field_activity ??
        startForm.errors.latitude ??
        startForm.errors.longitude,
);

const endError = computed(
    () =>
        locationError.value ??
        (endForm.errors as Partial<Record<'field_activity', string>>)
            .field_activity ??
        endForm.errors.latitude ??
        endForm.errors.longitude,
);

async function start(): Promise<void> {
    startForm.clearErrors();

    const location = await readLocation();

    if (!location) {
        return;
    }

    startForm.latitude = location.latitude;
    startForm.longitude = location.longitude;
    startForm.accuracy = location.accuracy;

    startForm.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => startForm.reset(),
    });
}

async function end(): Promise<void> {
    if (!props.openActivity) {
        return;
    }

    endForm.clearErrors();

    const location = await readLocation();

    if (!location) {
        return;
    }

    endForm.latitude = location.latitude;
    endForm.longitude = location.longitude;
    endForm.accuracy = location.accuracy;

    endForm.patch(update.url(props.openActivity.id), {
        preserveScroll: true,
        onSuccess: () => endForm.reset(),
    });
}
</script>

<template>
    <PortalPanel title="Field activity" :icon="Route">
        <template #description>
            Start an activity when you begin and end it when you finish. You can
            record several in a day.
        </template>

        <div class="space-y-6">
            <template v-if="openActivity">
                <div
                    class="border-l-4 border-brand-600 bg-brand-50 px-5 py-4 dark:bg-brand-950/60"
                >
                    <p
                        class="font-display text-[0.9rem] font-bold tracking-[0.12em] text-brand-800 uppercase dark:text-brand-200"
                    >
                        In progress since
                        <span class="tabular-nums">{{
                            openActivity.started_time
                        }}</span>
                    </p>
                    <p class="mt-1 font-heading text-xl font-bold">
                        {{ openActivity.activity }}
                    </p>
                    <p class="mt-1 text-muted-foreground">
                        {{ openActivity.programme }} ·
                        {{ openActivity.location }} ·
                        {{ openActivity.date }}
                    </p>
                    <p
                        v-if="openActivity.description"
                        class="mt-3 border-t border-brand-200/70 pt-3 leading-relaxed dark:border-brand-800"
                    >
                        {{ openActivity.description }}
                    </p>
                </div>

                <div>
                    <SiteButton
                        size="lg"
                        variant="donate"
                        class="w-full sm:w-auto sm:min-w-56"
                        :disabled="busy"
                        data-test="end-activity-button"
                        @click="end"
                    >
                        <Spinner v-if="busy" />
                        {{
                            locating ? 'Finding your location…' : 'End activity'
                        }}
                    </SiteButton>

                    <InputError :message="endError" class="mt-3" />
                </div>
            </template>

            <form
                v-else
                class="grid gap-5 sm:grid-cols-2"
                @submit.prevent="start"
            >
                <div class="sm:col-span-2">
                    <label for="programme" class="portal-label-text"
                        >Programme or project</label
                    >
                    <select
                        id="programme"
                        v-model="startForm.programme"
                        class="portal-input"
                        :aria-invalid="!!startForm.errors.programme"
                    >
                        <option value="" disabled>Choose a programme</option>
                        <option
                            v-for="programme in programmes"
                            :key="programme"
                            :value="programme"
                        >
                            {{ programme }}
                        </option>
                    </select>
                    <InputError
                        :message="startForm.errors.programme"
                        class="mt-1.5"
                    />
                </div>

                <div>
                    <label for="activity" class="portal-label-text"
                        >Activity</label
                    >
                    <input
                        id="activity"
                        v-model="startForm.activity"
                        type="text"
                        class="portal-input"
                        placeholder="For example: uniform distribution"
                        maxlength="160"
                        :aria-invalid="!!startForm.errors.activity"
                    />
                    <InputError
                        :message="startForm.errors.activity"
                        class="mt-1.5"
                    />
                </div>

                <div>
                    <label for="location" class="portal-label-text"
                        >Location</label
                    >
                    <input
                        id="location"
                        v-model="startForm.location"
                        type="text"
                        class="portal-input"
                        placeholder="Community, school or venue"
                        maxlength="160"
                        :aria-invalid="!!startForm.errors.location"
                    />
                    <InputError
                        :message="startForm.errors.location"
                        class="mt-1.5"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="portal-label-text">
                        Brief description
                        <span class="font-normal text-muted-foreground"
                            >(optional)</span
                        >
                    </label>
                    <textarea
                        id="description"
                        v-model="startForm.description"
                        rows="3"
                        maxlength="2000"
                        class="portal-input"
                        :aria-invalid="!!startForm.errors.description"
                    />
                    <InputError
                        :message="startForm.errors.description"
                        class="mt-1.5"
                    />
                </div>

                <div class="sm:col-span-2">
                    <SiteButton
                        type="submit"
                        size="lg"
                        class="w-full sm:w-auto sm:min-w-56"
                        :disabled="busy"
                        data-test="start-activity-button"
                    >
                        <Spinner v-if="busy" />
                        {{
                            locating
                                ? 'Finding your location…'
                                : 'Start activity'
                        }}
                    </SiteButton>

                    <InputError :message="startLocationError" class="mt-3" />
                </div>
            </form>

            <LocationNote>
                Your location is read only when an activity starts and when it
                ends.
            </LocationNote>
        </div>
    </PortalPanel>
</template>
