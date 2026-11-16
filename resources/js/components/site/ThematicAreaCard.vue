<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import type { ThematicArea } from '@/lib/siteContent';
import work from '@/routes/work';

type Props = {
    area: ThematicArea;
};

defineProps<Props>();
</script>

<template>
    <Link
        :href="`${work.index.url()}#${area.slug}`"
        class="group flex h-full flex-col border-t-[5px] bg-cream/60 transition-colors hover:bg-cream"
        :class="area.tone.border"
    >
        <div class="aspect-[3/2] overflow-hidden">
            <img
                :src="area.image.src"
                :alt="area.image.alt"
                loading="lazy"
                class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
            />
        </div>
        <div class="flex flex-1 flex-col p-6">
            <p
                class="font-display text-lg leading-none font-bold"
                :class="area.tone.text"
            >
                0{{ area.number }}
            </p>
            <h3
                class="mt-2 font-heading text-[1.35rem] leading-snug font-bold text-ink"
            >
                {{ area.title }}
            </h3>
            <p class="mt-3 flex-1 leading-relaxed text-stone-700">
                {{ area.summary }}
            </p>
            <span
                class="mt-5 inline-flex items-center gap-1 font-bold text-brand-700 group-hover:underline"
            >
                Learn more
                <ChevronRight class="size-4 text-sun-500" />
            </span>
        </div>
    </Link>
</template>
