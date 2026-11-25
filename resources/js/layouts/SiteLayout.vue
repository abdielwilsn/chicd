<script setup lang="ts">
import { onMounted } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import SiteUtilityBar from '@/components/site/SiteUtilityBar.vue';
import { startGoogleTranslate } from '@/lib/googleTranslate';

onMounted(startGoogleTranslate);
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-white font-body text-[1.0625rem] text-ink antialiased"
    >
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-[3px] focus:bg-brand-700 focus:px-4 focus:py-2 focus:text-white"
            >Skip to main content</a
        >
        <SiteUtilityBar />
        <SiteHeader />
        <main id="main-content" class="flex-1">
            <slot />
        </main>
        <SiteFooter />
    </div>
</template>
