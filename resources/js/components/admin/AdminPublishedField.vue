<script setup lang="ts">
type Props = {
    /** What "published" means for this kind of content. */
    description?: string;
};

withDefaults(defineProps<Props>(), {
    description: 'Untick to keep it as a draft that visitors cannot see.',
});

const published = defineModel<boolean>({ required: true });
</script>

<!-- The "show on the website" checkbox for admin content. -->
<template>
    <fieldset class="space-y-3">
        <legend class="portal-label-text">On the website</legend>
        <label class="flex items-start gap-3">
            <input v-model="published" type="checkbox" class="mt-1" />
            <span>
                <span class="font-semibold">Published</span>
                <span class="block text-[0.95rem] text-muted-foreground">{{
                    description
                }}</span>
            </span>
        </label>
        <slot />
    </fieldset>
</template>
