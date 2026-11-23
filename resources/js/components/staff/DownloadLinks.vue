<script setup lang="ts">
import { Download } from '@lucide/vue';
import { siteButtonVariants } from '@/components/site/siteButtonVariants';
import type { SiteButtonVariants } from '@/components/site/siteButtonVariants';

type Props = {
    pdfUrl: string;
    csvUrl: string;
    variant?: SiteButtonVariants['variant'];
};

withDefaults(defineProps<Props>(), {
    variant: 'outline',
});
</script>

<!--
    PDF and CSV download buttons. Plain links, not Inertia links, so the
    browser downloads the file instead of Inertia requesting it.
-->
<template>
    <div class="flex flex-wrap gap-2">
        <a :href="pdfUrl" :class="siteButtonVariants({ variant, size: 'sm' })">
            <Download />
            PDF
        </a>
        <a :href="csvUrl" :class="siteButtonVariants({ variant, size: 'sm' })">
            <Download />
            Excel / CSV
        </a>
    </div>
</template>
