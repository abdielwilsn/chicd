<script setup lang="ts">
type Props = {
    inverted?: boolean;
    showTagline?: boolean;
};

withDefaults(defineProps<Props>(), {
    inverted: false,
    showTagline: true,
});
</script>

<template>
    <span class="flex items-center gap-3">
        <svg
            viewBox="0 0 48 48"
            class="size-11 shrink-0 sm:size-12"
            aria-hidden="true"
        >
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
        <span class="flex flex-col">
            <span
                class="font-display text-[1.9rem] leading-[0.85] font-extrabold tracking-[0.02em]"
                :class="inverted ? 'text-white' : 'text-brand-900'"
                >CHICD</span
            >
            <span
                v-if="showTagline"
                class="mt-1 hidden font-body text-[0.72rem] leading-tight sm:block"
                :class="inverted ? 'text-brand-200' : 'text-stone-500'"
                >Innovating for Health,<br />Empowering for Life.</span
            >
        </span>
    </span>
</template>
