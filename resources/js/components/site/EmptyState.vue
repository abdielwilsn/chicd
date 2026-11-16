<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';

type Props = {
    icon: LucideIcon;
    title: string;
};

defineProps<Props>();
</script>

<template>
    <div class="border-t-[5px] border-sun-400 bg-cream px-6 py-10 sm:px-10">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
            <component :is="icon" class="size-8 shrink-0 text-brand-600" />
            <div>
                <h3 class="font-heading text-xl font-bold text-ink">
                    {{ title }}
                </h3>
                <div
                    v-if="$slots.default"
                    class="mt-2 max-w-2xl leading-relaxed text-stone-700"
                >
                    <slot />
                </div>
                <div v-if="$slots.actions" class="mt-6 flex flex-wrap gap-3">
                    <slot name="actions" />
                </div>
            </div>
        </div>
    </div>
</template>
