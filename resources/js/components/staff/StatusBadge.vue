<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

type Props = {
    status: string;
    label: string;
};

const props = defineProps<Props>();

const tone = computed(() => {
    switch (props.status) {
        case 'approved':
        case 'resolved':
        case 'successful':
        case 'paid':
            return 'bg-brand-100 text-brand-900 dark:bg-brand-900 dark:text-brand-100';
        case 'declined':
        case 'failed':
        case 'abandoned':
        case 'cancelled':
            return 'bg-secondary text-muted-foreground';
        default:
            return 'bg-sun-100 text-sun-700 dark:bg-sun-700/40 dark:text-sun-100';
    }
});
</script>

<!-- A small label for a timesheet, correction, leave, payroll or donation status. -->
<template>
    <span
        :class="
            cn(
                'inline-flex items-center rounded-[3px] px-2 py-0.5 font-display text-[0.8rem] font-bold tracking-[0.1em] whitespace-nowrap uppercase',
                tone,
            )
        "
    >
        {{ label }}
    </span>
</template>
