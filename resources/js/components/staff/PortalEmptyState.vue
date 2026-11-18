<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';

type Props = {
    icon: LucideIcon;
    title: string;
};

defineProps<Props>();
</script>

<!-- The portal version of the site's EmptyState. -->
<template>
    <div
        class="flex gap-4 border-t-[5px] border-sun-400 bg-secondary px-5 py-7 text-secondary-foreground sm:px-6"
    >
        <component
            :is="icon"
            class="size-7 shrink-0 text-brand-600 dark:text-brand-300"
        />
        <div>
            <p class="font-heading text-lg font-bold">{{ title }}</p>
            <div
                v-if="$slots.default"
                class="mt-1 leading-relaxed text-muted-foreground"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
