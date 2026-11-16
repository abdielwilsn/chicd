<script setup lang="ts">
import type { SiteImage } from '@/lib/siteContent';

type Props = {
    image: SiteImage;
    /** A solid block offset behind the photo, as in printed reports. */
    offset?: 'sun' | 'brand' | 'cream' | 'none';
    /** Aspect ratio and other classes for the image itself. */
    imageClass?: string;
};

withDefaults(defineProps<Props>(), {
    offset: 'sun',
    imageClass: 'aspect-[4/3]',
});

const offsetColours = {
    sun: 'bg-sun-400',
    brand: 'bg-brand-700',
    cream: 'bg-cream-deep',
    none: '',
} as const;
</script>

<template>
    <div
        class="relative"
        :class="offset !== 'none' ? 'mr-3 mb-3 sm:mr-5 sm:mb-5' : ''"
    >
        <div
            v-if="offset !== 'none'"
            class="absolute inset-0 translate-x-3 translate-y-3 sm:translate-x-5 sm:translate-y-5"
            :class="offsetColours[offset]"
            aria-hidden="true"
        />
        <img
            :src="image.src"
            :alt="image.alt"
            loading="lazy"
            class="relative w-full object-cover"
            :class="imageClass"
        />
    </div>
</template>
