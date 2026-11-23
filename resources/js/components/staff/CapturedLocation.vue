<script setup lang="ts">
import { MapPin } from '@lucide/vue';
import { computed } from 'vue';
import type { CapturedLocation } from '@/types';

const props = defineProps<{
    label: string;
    location: CapturedLocation | null;
    withinGeofence?: boolean | null;
}>();

const mapUrl = computed(() =>
    props.location
        ? `https://www.openstreetmap.org/?mlat=${props.location.latitude}&mlon=${props.location.longitude}#map=17/${props.location.latitude}/${props.location.longitude}`
        : null,
);
</script>

<!--
    The location captured at one moment (clock-in, clock-out, or the start or
    end of a field activity). Only these moments are ever captured.
-->
<template>
    <p class="flex flex-wrap items-center gap-x-2 text-[0.9rem]">
        <MapPin class="size-4 shrink-0 text-brand-600 dark:text-brand-300" />
        <span class="font-semibold">{{ label }}:</span>
        <template v-if="location">
            <a
                :href="mapUrl!"
                target="_blank"
                rel="noopener noreferrer"
                class="text-brand-700 tabular-nums underline-offset-4 hover:underline dark:text-brand-200"
            >
                {{ location.latitude.toFixed(5) }},
                {{ location.longitude.toFixed(5) }}
            </a>
            <span
                v-if="location.accuracy_metres !== null"
                class="text-muted-foreground"
                >±{{ location.accuracy_metres }} m</span
            >
            <span
                v-if="location.distance_metres !== null"
                :class="
                    withinGeofence === false
                        ? 'text-sun-700 dark:text-sun-300'
                        : 'text-muted-foreground'
                "
            >
                · {{ location.distance_metres }} m from the office{{
                    withinGeofence === false ? ', outside the office area' : ''
                }}
            </span>
        </template>
        <span v-else class="text-muted-foreground"
            >Not captured (set by an admin or still open)</span
        >
    </p>
</template>
