<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import type { SiteImage } from '@/lib/siteContent';

type Props = {
    href: string;
    title: string;
    description?: string;
    image?: SiteImage;
    label?: string;
    cta?: string;
    /** Colour of the rule along the top of the card. */
    accent?: string;
};

withDefaults(defineProps<Props>(), {
    description: undefined,
    image: undefined,
    label: undefined,
    cta: 'Learn more',
    accent: 'border-sun-400',
});
</script>

<template>
    <Link
        :href="href"
        class="group flex h-full flex-col border-t-[5px] bg-cream/60 transition-colors hover:bg-cream"
        :class="accent"
    >
        <div v-if="image" class="aspect-[16/10] overflow-hidden">
            <img
                :src="image.src"
                :alt="image.alt"
                loading="lazy"
                class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
            />
        </div>
        <div class="flex flex-1 flex-col p-6">
            <p
                v-if="label"
                class="mb-2 font-display text-[0.9rem] font-bold tracking-[0.12em] text-brand-700 uppercase"
            >
                {{ label }}
            </p>
            <h3 class="font-heading text-xl leading-snug font-bold text-ink">
                {{ title }}
            </h3>
            <p
                v-if="description"
                class="mt-2 flex-1 leading-relaxed text-stone-700"
            >
                {{ description }}
            </p>
            <span
                class="mt-5 inline-flex items-center gap-1 font-bold text-brand-700 group-hover:underline"
            >
                {{ cta }}
                <ChevronRight class="size-4 text-sun-500" />
            </span>
        </div>
    </Link>
</template>
