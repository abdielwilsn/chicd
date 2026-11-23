<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/admin/corrections';
import { show } from '@/routes/admin/timesheets';
import type { CorrectionRequest } from '@/types';

const props = defineProps<{
    request: CorrectionRequest;
    /** Link the staff member's name to their timesheet for that month. */
    linkToTimesheet?: boolean;
}>();

const closing = ref(false);

const form = useForm({
    status: 'declined' as 'resolved' | 'declined',
    note: '',
});

function close(status: 'resolved' | 'declined'): void {
    form.status = status;
    form.patch(update.url(props.request.id), {
        preserveScroll: true,
        onSuccess: () => {
            closing.value = false;
            form.reset();
        },
    });
}
</script>

<!-- One correction request as an admin sees it, with ways to close it. -->
<template>
    <li class="space-y-2 px-4 py-4 sm:px-5">
        <div class="flex flex-wrap items-center gap-2">
            <StatusBadge
                :status="request.status"
                :label="request.status_label"
            />
            <Link
                v-if="linkToTimesheet && request.staff_name"
                :href="
                    show({
                        user: request.staff_id,
                        month: request.work_date.slice(0, 7),
                    })
                "
                class="font-heading font-bold text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
            >
                {{ request.staff_name }}
            </Link>
            <span class="font-heading font-bold">{{
                request.work_date_label
            }}</span>
            <span class="text-muted-foreground">· {{ request.record }}</span>
            <span class="ml-auto text-[0.9rem] text-muted-foreground"
                >Requested {{ request.requested_at }}</span
            >
        </div>

        <p class="leading-relaxed">{{ request.details }}</p>

        <p
            v-if="request.resolution_note"
            class="border-l-4 border-brand-600 bg-brand-50 px-3 py-2 text-[0.95rem] dark:bg-brand-950/60"
        >
            <span class="font-semibold"
                >{{ request.resolved_by ?? 'Admin' }}:</span
            >
            {{ request.resolution_note }}
        </p>

        <template v-if="request.status === 'pending'">
            <p class="text-[0.9rem] text-muted-foreground">
                To fix a record, use "Correct" on it in the staff member's
                timesheet and choose this request. Or close it here.
            </p>
            <SiteButton v-if="!closing" variant="link" @click="closing = true"
                >Close without editing a record</SiteButton
            >
            <form v-else class="space-y-3" @submit.prevent>
                <label
                    :for="`close-note-${request.id}`"
                    class="portal-label-text"
                    >Note for the staff member</label
                >
                <textarea
                    :id="`close-note-${request.id}`"
                    v-model="form.note"
                    rows="2"
                    maxlength="1000"
                    class="portal-input"
                />
                <InputError :message="form.errors.note" />
                <div class="flex flex-wrap gap-2">
                    <SiteButton
                        size="sm"
                        :disabled="form.processing"
                        @click="close('resolved')"
                    >
                        <Spinner
                            v-if="form.processing && form.status === 'resolved'"
                        />
                        Mark resolved
                    </SiteButton>
                    <SiteButton
                        size="sm"
                        variant="outline"
                        :disabled="form.processing"
                        @click="close('declined')"
                    >
                        <Spinner
                            v-if="form.processing && form.status === 'declined'"
                        />
                        Decline
                    </SiteButton>
                    <SiteButton
                        size="sm"
                        variant="link"
                        @click="closing = false"
                        >Cancel</SiteButton
                    >
                </div>
            </form>
        </template>
    </li>
</template>
