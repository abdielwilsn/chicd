<script setup lang="ts">
import InputError from '@/components/InputError.vue';

type Props = {
    id: string;
    label: string;
    /** Shown after the label in muted text, e.g. "(optional)". */
    note?: string;
    /** Guidance shown under the label. */
    hint?: string;
    error?: string;
};

defineProps<Props>();
</script>

<!-- A labelled admin form field with guidance and its validation error. -->
<template>
    <div>
        <label :for="id" class="portal-label-text">
            {{ label }}
            <span v-if="note" class="font-normal text-muted-foreground">{{
                note
            }}</span>
        </label>
        <p v-if="hint" class="mb-2 text-[0.95rem] text-muted-foreground">
            {{ hint }}
        </p>
        <slot />
        <InputError :message="error" class="mt-1.5" />
    </div>
</template>
