<script setup lang="ts">
type Props = {
    eyebrow?: string;
    title: string;
};

withDefaults(defineProps<Props>(), {
    eyebrow: 'Staff portal',
});
</script>

<template>
    <section class="border-b-[6px] border-sun-400 bg-brand-900 text-white">
        <div
            class="portal-container flex flex-col gap-6 py-9 sm:flex-row sm:items-end sm:justify-between sm:py-12"
        >
            <div class="min-w-0">
                <p
                    class="mb-2 font-display text-[0.95rem] font-bold tracking-[0.14em] text-sun-300 uppercase"
                >
                    {{ eyebrow }}
                </p>
                <h1
                    class="font-display text-[2.4rem] leading-[0.95] font-extrabold tracking-[0.005em] uppercase sm:text-[3.1rem]"
                >
                    {{ title }}
                </h1>
                <div
                    v-if="$slots.default"
                    class="mt-3 text-[1.0625rem] leading-relaxed text-brand-100"
                >
                    <slot />
                </div>
            </div>
            <div v-if="$slots.actions" class="shrink-0">
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>
