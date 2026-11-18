<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-14 shrink-0 items-center gap-2 border-b border-border bg-card px-5 transition-[width,height] ease-linear md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger
                class="-ml-1 text-brand-800 hover:bg-secondary dark:text-brand-200"
            />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
    </header>
</template>
