<script setup lang="ts">
import SiteButton from '@/components/site/SiteButton.vue';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    submitLabel: string;
    cancelHref: string;
    processing: boolean;
    progress?: { percentage?: number } | null;
};

defineProps<Props>();
</script>

<!-- Save and cancel buttons, with upload progress, for an admin form. -->
<template>
    <div class="grid gap-3">
        <div class="flex flex-wrap gap-3">
            <SiteButton type="submit" :disabled="processing">
                <Spinner v-if="processing" />
                {{ submitLabel }}
            </SiteButton>
            <SiteButton :href="cancelHref" variant="outline">Cancel</SiteButton>
        </div>
        <progress
            v-if="progress"
            :value="progress.percentage"
            max="100"
            class="w-full"
        />
    </div>
</template>
