<script setup lang="ts">
import type { SiteImage } from '@/lib/siteContent';

type Props = {
    image: SiteImage;
    tone?: 'brand' | 'cream' | 'white';
    /** Place the photograph on the right on large screens. */
    reverse?: boolean;
};

withDefaults(defineProps<Props>(), {
    tone: 'cream',
    reverse: false,
});

const tones = {
    brand: 'bg-brand-900 text-brand-100',
    cream: 'bg-cream text-stone-700',
    white: 'border border-stone-200 bg-white text-stone-700',
} as const;
</script>

<template>
    <article class="-mx-5 grid sm:mx-0 lg:grid-cols-2" :class="tones[tone]">
        <div
            class="relative min-h-64 sm:min-h-[24rem]"
            :class="{ 'lg:order-2': reverse }"
        >
            <img
                :src="image.src"
                :alt="image.alt"
                loading="lazy"
                class="absolute inset-0 size-full object-cover"
            />
        </div>
        <div
            class="flex flex-col justify-center px-5 py-10 sm:px-12 sm:py-14 lg:px-14 lg:py-16"
        >
            <slot />
        </div>
    </article>
</template>
