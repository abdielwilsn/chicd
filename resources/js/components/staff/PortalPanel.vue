<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';

type Props = {
    title?: string;
    icon?: LucideIcon;
};

defineProps<Props>();
</script>

<!--
    The portal version of the site's FormPanel: a white panel with a marigold
    top rule and a soft shadow.
-->
<template>
    <div
        class="border-t-[6px] border-sun-400 bg-card px-5 py-7 text-card-foreground shadow-[0_20px_40px_-24px_rgb(10_36_25/0.35)] ring-1 ring-border sm:px-8 sm:py-8"
    >
        <div v-if="title" class="mb-6">
            <h2
                class="flex items-center gap-3 font-heading text-[1.6rem] leading-tight font-bold"
            >
                <component
                    :is="icon"
                    v-if="icon"
                    class="size-6 shrink-0 text-brand-600 dark:text-brand-300"
                />
                {{ title }}
            </h2>
            <div
                v-if="$slots.description"
                class="mt-2 leading-relaxed text-muted-foreground"
            >
                <slot name="description" />
            </div>
        </div>
        <slot />
    </div>
</template>
