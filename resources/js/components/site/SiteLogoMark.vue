<script setup lang="ts">
type Props = {
    inverted?: boolean;
};

withDefaults(defineProps<Props>(), {
    inverted: false,
});
</script>

<template>
    <svg viewBox="0 0 48 48" class="shrink-0" aria-hidden="true">
        <circle
            cx="24"
            cy="24"
            r="23"
            :class="inverted ? 'fill-white' : 'fill-brand-700'"
        />
        <path
            d="M20.5 11h7v9.5H37v7h-9.5V37h-7v-9.5H11v-7h9.5z"
            :class="inverted ? 'fill-brand-700' : 'fill-white'"
        />
        <path
            d="M29.5 18.5c.4-4.6 3.4-7.6 8-8 .5 0 .9.4.8.9-.5 4.4-3.5 7.3-8 7.9-.5.1-.9-.3-.8-.8z"
            class="fill-sun-400"
        />
    </svg>
</template>
