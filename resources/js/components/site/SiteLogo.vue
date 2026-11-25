<script setup lang="ts">
import SiteLogoMark from '@/components/site/SiteLogoMark.vue';

type Props = {
    inverted?: boolean;
    showTagline?: boolean;
};

withDefaults(defineProps<Props>(), {
    inverted: false,
    showTagline: true,
});
</script>

<template>
    <span class="flex items-center gap-3">
        <SiteLogoMark :inverted="inverted" class="size-11 sm:size-12" />
        <span class="flex flex-col">
            <span
                class="notranslate font-display text-[1.9rem] leading-[0.85] font-extrabold tracking-[0.02em]"
                :class="inverted ? 'text-white' : 'text-brand-900'"
                translate="no"
                >CHICD</span
            >
            <span
                v-if="showTagline"
                class="mt-1 hidden font-body text-[0.72rem] leading-tight sm:block"
                :class="inverted ? 'text-brand-200' : 'text-stone-500'"
                >Innovating for Health,<br />Empowering for Life.</span
            >
        </span>
    </span>
</template>
