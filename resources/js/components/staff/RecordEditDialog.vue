<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
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
import { update as updateFieldActivity } from '@/routes/admin/field-activities';
import { update as updateOfficeAttendance } from '@/routes/admin/office-attendances';
import type {
    AdminFieldActivity,
    AdminOfficeAttendance,
    CorrectionRequest,
} from '@/types';

const props = defineProps<{
    record:
        | { type: 'office_attendance'; data: AdminOfficeAttendance }
        | { type: 'field_activity'; data: AdminFieldActivity }
        | null;
    /** The month's pending requests, any of which this change may answer. */
    pendingRequests: CorrectionRequest[];
    programmes: string[];
}>();

const open = defineModel<boolean>('open', { required: true });

const form = useForm({
    clocked_in_at: '',
    clocked_out_at: '' as string | null,
    started_at: '',
    ended_at: '' as string | null,
    programme: '',
    activity: '',
    location: '',
    description: '' as string | null,
    reason: '',
    correction_request_id: null as number | null,
});

const isOffice = computed(() => props.record?.type === 'office_attendance');

const programmeOptions = computed(() => {
    const current =
        props.record?.type === 'field_activity'
            ? props.record.data.edit.programme
            : null;

    return current && !props.programmes.includes(current)
        ? [...props.programmes, current]
        : props.programmes;
});

/** Requests about this record first, then requests about missing days. */
const answerableRequests = computed(() =>
    props.pendingRequests.filter(
        (request) =>
            request.record_type === null ||
            (request.record_type === props.record?.type &&
                request.record_id === props.record?.data.id),
    ),
);

watch(open, (isOpen) => {
    if (!isOpen || !props.record) {
        return;
    }

    form.clearErrors();
    form.reason = '';

    const matching = props.pendingRequests.find(
        (request) =>
            request.record_type === props.record?.type &&
            request.record_id === props.record?.data.id,
    );
    form.correction_request_id = matching?.id ?? null;

    if (props.record.type === 'office_attendance') {
        form.clocked_in_at = props.record.data.edit.clocked_in_at;
        form.clocked_out_at = props.record.data.edit.clocked_out_at ?? '';
    } else {
        Object.assign(form, {
            ...props.record.data.edit,
            ended_at: props.record.data.edit.ended_at ?? '',
            description: props.record.data.edit.description ?? '',
        });
    }
});

const recordError = computed(
    () => (form.errors as Partial<Record<'record', string>>).record,
);

function submit(): void {
    if (!props.record) {
        return;
    }

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };

    if (props.record.type === 'office_attendance') {
        form.transform((data) => ({
            clocked_in_at: data.clocked_in_at,
            clocked_out_at: data.clocked_out_at || null,
            reason: data.reason,
            correction_request_id: data.correction_request_id,
        })).patch(updateOfficeAttendance.url(props.record.data.id), options);
    } else {
        form.transform((data) => ({
            started_at: data.started_at,
            ended_at: data.ended_at || null,
            programme: data.programme,
            activity: data.activity,
            location: data.location,
            description: data.description || null,
            reason: data.reason,
            correction_request_id: data.correction_request_id,
        })).patch(updateFieldActivity.url(props.record.data.id), options);
    }
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-xl">
            <form v-if="record" class="space-y-5" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle class="font-heading text-xl">
                        Correct
                        {{ isOffice ? 'office session' : 'field activity' }}
                    </DialogTitle>
                    <DialogDescription>
                        Times are Nigerian time. The location the staff member
                        captured is not changed. Your reason is kept in the
                        audit trail.
                    </DialogDescription>
                </DialogHeader>

                <InputError :message="recordError" />

                <div v-if="isOffice" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit-in" class="portal-label-text"
                            >Clock in</label
                        >
                        <input
                            id="edit-in"
                            v-model="form.clocked_in_at"
                            type="datetime-local"
                            class="portal-input"
                        />
                        <InputError
                            :message="form.errors.clocked_in_at"
                            class="mt-1.5"
                        />
                    </div>
                    <div>
                        <label for="edit-out" class="portal-label-text"
                            >Clock out</label
                        >
                        <input
                            id="edit-out"
                            v-model="form.clocked_out_at"
                            type="datetime-local"
                            class="portal-input"
                        />
                        <InputError
                            :message="form.errors.clocked_out_at"
                            class="mt-1.5"
                        />
                    </div>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit-start" class="portal-label-text"
                            >Started</label
                        >
                        <input
                            id="edit-start"
                            v-model="form.started_at"
                            type="datetime-local"
                            class="portal-input"
                        />
                        <InputError
                            :message="form.errors.started_at"
                            class="mt-1.5"
                        />
                    </div>
                    <div>
                        <label for="edit-end" class="portal-label-text"
                            >Ended</label
                        >
                        <input
                            id="edit-end"
                            v-model="form.ended_at"
                            type="datetime-local"
                            class="portal-input"
                        />
                        <InputError
                            :message="form.errors.ended_at"
                            class="mt-1.5"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="edit-programme" class="portal-label-text"
                            >Programme or project</label
                        >
                        <select
                            id="edit-programme"
                            v-model="form.programme"
                            class="portal-input"
                        >
                            <option
                                v-for="programme in programmeOptions"
                                :key="programme"
                                :value="programme"
                            >
                                {{ programme }}
                            </option>
                        </select>
                        <InputError
                            :message="form.errors.programme"
                            class="mt-1.5"
                        />
                    </div>
                    <div>
                        <label for="edit-activity" class="portal-label-text"
                            >Activity</label
                        >
                        <input
                            id="edit-activity"
                            v-model="form.activity"
                            type="text"
                            maxlength="160"
                            class="portal-input"
                        />
                        <InputError
                            :message="form.errors.activity"
                            class="mt-1.5"
                        />
                    </div>
                    <div>
                        <label for="edit-location" class="portal-label-text"
                            >Location</label
                        >
                        <input
                            id="edit-location"
                            v-model="form.location"
                            type="text"
                            maxlength="160"
                            class="portal-input"
                        />
                        <InputError
                            :message="form.errors.location"
                            class="mt-1.5"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="edit-description" class="portal-label-text"
                            >Description</label
                        >
                        <textarea
                            id="edit-description"
                            v-model="form.description"
                            rows="2"
                            maxlength="2000"
                            class="portal-input"
                        />
                    </div>
                </div>

                <div v-if="answerableRequests.length">
                    <label for="edit-request" class="portal-label-text"
                        >Answers correction request</label
                    >
                    <select
                        id="edit-request"
                        v-model="form.correction_request_id"
                        class="portal-input"
                    >
                        <option :value="null">None</option>
                        <option
                            v-for="request in answerableRequests"
                            :key="request.id"
                            :value="request.id"
                        >
                            {{ request.work_date_label }}:
                            {{ request.details.slice(0, 70) }}
                        </option>
                    </select>
                    <InputError
                        :message="form.errors.correction_request_id"
                        class="mt-1.5"
                    />
                </div>

                <div>
                    <label for="edit-reason" class="portal-label-text"
                        >Reason for the change</label
                    >
                    <textarea
                        id="edit-reason"
                        v-model="form.reason"
                        rows="2"
                        maxlength="1000"
                        class="portal-input"
                        placeholder="For example: forgot to clock out; confirmed with line manager."
                    />
                    <InputError :message="form.errors.reason" class="mt-1.5" />
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
                        Save correction
                    </SiteButton>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
