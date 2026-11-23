<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/attendance/corrections';
import type { CorrectableRecord } from '@/types';

const props = defineProps<{
    /** The record the request is about, or null for a day with nothing recorded. */
    record: CorrectableRecord | null;
    /** The latest day that can be chosen for a missing record (YYYY-MM-DD). */
    maxDate: string;
}>();

const open = defineModel<boolean>('open', { required: true });

const form = useForm({
    record_type: null as CorrectableRecord['type'] | null,
    record_id: null as number | null,
    work_date: '',
    details: '',
});

watch(open, (isOpen) => {
    if (isOpen) {
        form.reset();
        form.clearErrors();
        form.record_type = props.record?.type ?? null;
        form.record_id = props.record?.id ?? null;
    }
});

function submit(): void {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-lg">
            <form class="space-y-5" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle class="font-heading text-xl">
                        {{
                            record
                                ? 'Request a correction'
                                : 'Report a missing record'
                        }}
                    </DialogTitle>
                    <DialogDescription>
                        An admin reviews every request. You cannot change your
                        records yourself, and any change is kept in an audit
                        trail.
                    </DialogDescription>
                </DialogHeader>

                <p
                    v-if="record"
                    class="border-l-4 border-sun-400 bg-secondary px-4 py-3 text-[0.95rem]"
                >
                    {{ record.summary }}
                </p>

                <div v-else>
                    <label for="correction-date" class="portal-label-text"
                        >Day</label
                    >
                    <input
                        id="correction-date"
                        v-model="form.work_date"
                        type="date"
                        :max="maxDate"
                        class="portal-input"
                        :aria-invalid="!!form.errors.work_date"
                    />
                    <InputError
                        :message="form.errors.work_date"
                        class="mt-1.5"
                    />
                </div>

                <div>
                    <label for="correction-details" class="portal-label-text"
                        >What needs correcting?</label
                    >
                    <textarea
                        id="correction-details"
                        v-model="form.details"
                        rows="4"
                        maxlength="2000"
                        class="portal-input"
                        placeholder="For example: I forgot to clock out. I left the office at 17:00."
                        :aria-invalid="!!form.errors.details"
                    />
                    <InputError
                        :message="form.errors.details ?? form.errors.record_id"
                        class="mt-1.5"
                    />
                </div>

                <div class="flex flex-wrap justify-end gap-2">
                    <SiteButton
                        variant="outline"
                        size="sm"
                        @click="open = false"
                        >Cancel</SiteButton
                    >
                    <SiteButton
                        type="submit"
                        size="sm"
                        :disabled="form.processing"
                    >
                        <Spinner v-if="form.processing" />
                        Send request
                    </SiteButton>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
