<script setup lang="ts">
import { computed } from 'vue';

type Props = {
    eyebrow?: string;
    title: string;
    tone?: 'brand' | 'sun' | 'cream';
};

const props = withDefaults(defineProps<Props>(), {
    eyebrow: undefined,
    tone: 'brand',
});

const tones = {
    brand: {
        section: 'bg-brand-800 text-white',
        label: 'text-sun-300',
        title: 'text-white',
        body: 'text-brand-100',
    },
    sun: {
        section: 'bg-sun-400 text-brand-950',
        label: 'text-brand-900',
        title: 'text-brand-950',
        body: 'text-brand-950/85',
    },
    cream: {
        section: 'bg-cream text-ink',
        label: 'text-brand-700',
        title: 'text-ink',
        body: 'text-stone-700',
    },
} as const;

const tone = computed(() => tones[props.tone]);
</script>

<template>
    <section :class="tone.section">
        <div
            class="site-container grid gap-8 py-16 sm:py-20 lg:grid-cols-12 lg:items-center lg:gap-12"
        >
            <div class="lg:col-span-7">
                <p v-if="eyebrow" class="site-label mb-4" :class="tone.label">
                    {{ eyebrow }}
                </p>
                <h2
                    class="font-heading text-[2rem] leading-[1.15] font-bold sm:text-[2.5rem]"
                    :class="tone.title"
                >
                    {{ title }}
                </h2>
                <div
                    v-if="$slots.default"
                    class="mt-4 max-w-2xl space-y-3 text-[1.0625rem] leading-relaxed sm:text-lg"
                    :class="tone.body"
                >
                    <slot />
                </div>
            </div>
            <div
                class="flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:col-span-5 lg:justify-end"
            >
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>
