<script setup lang="ts">
import FeatureSplit from '@/components/site/FeatureSplit.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import type { AddedProgramme } from '@/types';

type Props = {
    programme: AddedProgramme;
    /** Alternate the colour and photo side down a list of programmes. */
    variant?: 'cream' | 'brand';
};

withDefaults(defineProps<Props>(), {
    variant: 'cream',
});
</script>

<!-- An admin-added programme, styled like the flagship programme features. -->
<template>
    <FeatureSplit
        :image="programme.image"
        :tone="variant"
        :reverse="variant === 'brand'"
    >
        <p
            v-if="programme.tagline"
            class="font-display text-[1rem] font-bold tracking-[0.12em] uppercase"
            :class="variant === 'brand' ? 'text-sun-300' : 'text-sun-700'"
        >
            {{ programme.tagline }}
        </p>
        <h3
            class="mt-3 font-heading text-[1.6rem] leading-tight font-bold sm:text-[2.2rem]"
            :class="variant === 'brand' ? 'text-white' : 'text-ink'"
        >
            {{ programme.name }}
        </h3>
        <div
            class="mt-5"
            :class="
                variant === 'brand' ? 'space-y-4 leading-[1.7]' : 'site-prose'
            "
        >
            <p v-for="paragraph in programme.summary" :key="paragraph">
                {{ paragraph }}
            </p>
        </div>
        <SiteButton
            :href="programme.url"
            :variant="variant === 'brand' ? 'linkLight' : 'link'"
            class="mt-7 self-start"
        >
            Learn About {{ programme.name }}
        </SiteButton>
    </FeatureSplit>
</template>
