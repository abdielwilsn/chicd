<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight, Clock, MapPin } from '@lucide/vue';
import type { Opportunity } from '@/types';

type Props = {
    opportunity: Opportunity;
};

defineProps<Props>();
</script>

<!-- An open opportunity in a listing. -->
<template>
    <Link
        :href="opportunity.url"
        class="group flex h-full flex-col border-t-[5px] border-brand-600 bg-cream/60 p-6 transition-colors hover:bg-cream"
    >
        <p
            class="mb-2 font-display text-[0.9rem] font-bold tracking-[0.12em] text-brand-700 uppercase"
        >
            {{ opportunity.type_label }}
        </p>
        <h3 class="font-heading text-xl leading-snug font-bold text-ink">
            {{ opportunity.title }}
        </h3>
        <ul class="mt-3 space-y-1 text-[0.95rem] text-stone-700">
            <li v-if="opportunity.location" class="flex items-center gap-2">
                <MapPin class="size-4 shrink-0 text-sun-600" />
                {{ opportunity.location }}
            </li>
            <li class="flex items-center gap-2">
                <Clock class="size-4 shrink-0 text-sun-600" />
                {{
                    opportunity.closes_on
                        ? `Closes ${opportunity.closes_on}`
                        : 'Open until filled'
                }}
            </li>
        </ul>
        <p class="mt-3 flex-1 leading-relaxed text-stone-700">
            {{ opportunity.summary }}
        </p>
        <span
            class="mt-5 inline-flex items-center gap-1 font-bold text-brand-700 group-hover:underline"
        >
            View details
            <ChevronRight class="size-4 text-sun-500" />
        </span>
    </Link>
</template>
