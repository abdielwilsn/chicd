<script setup lang="ts">
import { MapPin } from '@lucide/vue';
</script>

<template>
    <p
        class="flex items-start gap-2 border-t border-border pt-4 text-[0.92rem] leading-snug text-muted-foreground"
    >
        <MapPin class="mt-0.5 size-4 shrink-0 text-sun-600" />
        <span><slot /></span>
    </p>
</template>
