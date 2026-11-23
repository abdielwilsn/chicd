<script setup lang="ts">
import AdminField from '@/components/admin/AdminField.vue';

type Props = {
    id: string;
    label: string;
    note?: string;
    /** The image already saved, shown until it is replaced or removed. */
    currentUrl?: string | null;
    /** Offer a checkbox to remove the current image. */
    removable?: boolean;
    error?: string;
};

withDefaults(defineProps<Props>(), {
    note: '(JPG, PNG or WebP, up to 4 MB)',
    currentUrl: null,
    removable: false,
    error: undefined,
});

const file = defineModel<File | null>({ default: null });
const remove = defineModel<boolean>('remove', { default: false });

function pick(event: Event): void {
    file.value = (event.target as HTMLInputElement).files?.[0] ?? null;
}
</script>

<!-- An image upload with a preview of the current image. -->
<template>
    <AdminField :id="id" :label="label" :note="note" :error="error">
        <img
            v-if="currentUrl && !remove"
            :src="currentUrl"
            alt=""
            class="mb-2 h-32 w-auto border border-border object-cover"
        />
        <p
            v-if="currentUrl && !remove"
            class="mb-2 text-[0.95rem] text-muted-foreground"
        >
            Choose a file only to replace it.
        </p>
        <input
            :id="id"
            type="file"
            accept="image/*"
            class="portal-input"
            @change="pick"
        />
        <label
            v-if="removable && currentUrl"
            class="mt-2 flex items-center gap-2 text-[0.95rem]"
        >
            <input v-model="remove" type="checkbox" />
            Remove the current image
        </label>
    </AdminField>
</template>
