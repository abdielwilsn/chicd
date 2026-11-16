<script setup lang="ts">
type Props = {
    eyebrow?: string;
    title: string;
    centered?: boolean;
    inverted?: boolean;
    as?: 'h2' | 'h3';
};

withDefaults(defineProps<Props>(), {
    eyebrow: undefined,
    centered: false,
    inverted: false,
    as: 'h2',
});
</script>

<template>
    <div :class="centered ? 'mx-auto max-w-3xl text-center' : 'max-w-3xl'">
        <p
            v-if="eyebrow"
            class="site-label mb-5"
            :class="[
                inverted ? 'text-sun-300' : '',
                centered
                    ? 'before:h-px before:flex-1 before:bg-current before:opacity-30'
                    : '',
            ]"
        >
            {{ eyebrow }}
        </p>
        <component
            :is="as"
            class="site-heading"
            :class="{ 'text-white': inverted }"
        >
            {{ title }}
        </component>
        <div
            v-if="$slots.default"
            class="mt-5 space-y-4 text-[1.0625rem] leading-[1.7] sm:text-lg"
            :class="inverted ? 'text-brand-100' : 'text-stone-700'"
        >
            <slot />
        </div>
    </div>
</template>
