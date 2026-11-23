<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';

type Props = {
    editHref: string;
    destroyUrl: string;
    /** The question asked before removing, e.g. 'Remove "Title"?' */
    confirmMessage: string;
};

const props = defineProps<Props>();

function remove(): void {
    if (!window.confirm(props.confirmMessage)) {
        return;
    }

    router.delete(props.destroyUrl, { preserveScroll: true });
}
</script>

<!-- Edit and remove actions for a row in an admin list. -->
<template>
    <div class="flex justify-end gap-4">
        <Link
            :href="editHref"
            class="inline-flex items-center gap-1.5 font-semibold text-brand-700 hover:underline dark:text-brand-200"
        >
            <Pencil class="size-4" />
            Edit
        </Link>
        <button
            type="button"
            class="inline-flex items-center gap-1.5 font-semibold text-destructive hover:underline"
            @click="remove"
        >
            <Trash2 class="size-4" />
            Remove
        </button>
    </div>
</template>
