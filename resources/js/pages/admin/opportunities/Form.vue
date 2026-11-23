<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminField from '@/components/admin/AdminField.vue';
import AdminFormActions from '@/components/admin/AdminFormActions.vue';
import AdminPublishedField from '@/components/admin/AdminPublishedField.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import { submitAdminForm } from '@/lib/adminForm';
import { index, store, update } from '@/routes/admin/opportunities';
import type { AdminOpportunity, Option } from '@/types';

const props = defineProps<{
    opportunity: AdminOpportunity | null;
    types: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Opportunities', href: index() },
            { title: 'Opportunity', href: '#' },
        ],
    },
});

const isNew = computed(() => props.opportunity === null);

const form = useForm({
    title: props.opportunity?.title ?? '',
    type: props.opportunity?.type ?? '',
    location: props.opportunity?.location ?? '',
    summary: props.opportunity?.summary ?? '',
    body: props.opportunity?.body_text ?? '',
    closes_on: props.opportunity?.closes_on_date ?? '',
    application_url: props.opportunity?.application_url ?? '',
    attachment: null as File | null,
    remove_attachment: false,
    is_published: props.opportunity?.is_published ?? true,
});

function pickAttachment(event: Event): void {
    form.attachment = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submit(): void {
    submitAdminForm(
        form,
        store.url(),
        props.opportunity ? update.url(props.opportunity.id) : null,
    );
}
</script>

<template>
    <Head
        :title="isNew ? 'Add an opportunity' : `Edit ${opportunity?.title}`"
    />

    <div>
        <PortalPageHeader
            eyebrow="Admin · Opportunities"
            :title="isNew ? 'Add an opportunity' : 'Edit opportunity'"
        />

        <div class="portal-container py-8 sm:py-10">
            <PortalPanel>
                <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
                    <AdminField
                        id="title"
                        label="Title"
                        :error="form.errors.title"
                    >
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            maxlength="200"
                            class="portal-input"
                            :aria-invalid="!!form.errors.title"
                        />
                    </AdminField>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <AdminField
                            id="type"
                            label="Type"
                            :error="form.errors.type"
                        >
                            <select
                                id="type"
                                v-model="form.type"
                                class="portal-input"
                                :aria-invalid="!!form.errors.type"
                            >
                                <option value="" disabled>Choose a type</option>
                                <option
                                    v-for="type in types"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </AdminField>
                        <AdminField
                            id="location"
                            label="Location"
                            note="(optional)"
                            :error="form.errors.location"
                        >
                            <input
                                id="location"
                                v-model="form.location"
                                type="text"
                                maxlength="150"
                                placeholder="e.g. Uyo, or Remote"
                                class="portal-input"
                            />
                        </AdminField>
                    </div>

                    <AdminField
                        id="summary"
                        label="Summary"
                        hint="One or two sentences shown in the list of opportunities."
                        :error="form.errors.summary"
                    >
                        <textarea
                            id="summary"
                            v-model="form.summary"
                            rows="3"
                            maxlength="1000"
                            class="portal-input"
                            :aria-invalid="!!form.errors.summary"
                        />
                    </AdminField>

                    <AdminField
                        id="body"
                        label="Full details"
                        note="(optional)"
                        hint="Responsibilities, requirements and how to apply. Leave a blank line between paragraphs."
                        :error="form.errors.body"
                    >
                        <textarea
                            id="body"
                            v-model="form.body"
                            rows="10"
                            maxlength="20000"
                            class="portal-input"
                        />
                    </AdminField>

                    <div class="grid gap-6 sm:grid-cols-[12rem_minmax(0,1fr)]">
                        <AdminField
                            id="closes_on"
                            label="Closing date"
                            note="(optional)"
                            :error="form.errors.closes_on"
                        >
                            <input
                                id="closes_on"
                                v-model="form.closes_on"
                                type="date"
                                class="portal-input"
                            />
                        </AdminField>
                        <AdminField
                            id="application_url"
                            label="Application link"
                            note="(optional)"
                            hint="Without one, visitors are asked to contact CHICD."
                            :error="form.errors.application_url"
                        >
                            <input
                                id="application_url"
                                v-model="form.application_url"
                                type="url"
                                maxlength="500"
                                placeholder="https://"
                                class="portal-input"
                            />
                        </AdminField>
                    </div>

                    <AdminField
                        id="attachment"
                        label="Document"
                        note="(optional: job description or terms of reference, PDF or Word, up to 10 MB)"
                        :error="form.errors.attachment"
                    >
                        <p
                            v-if="
                                opportunity?.attachment_url &&
                                !form.remove_attachment
                            "
                            class="mb-2 text-[0.95rem] text-muted-foreground"
                        >
                            Current:
                            <a
                                :href="opportunity.attachment_url"
                                target="_blank"
                                rel="noopener"
                                class="font-semibold text-brand-700 hover:underline dark:text-brand-200"
                                >download</a
                            ><template v-if="opportunity.attachment_size">
                                · {{ opportunity.attachment_size }}</template
                            >. Choose a file only to replace it.
                        </p>
                        <input
                            id="attachment"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            class="portal-input"
                            @change="pickAttachment"
                        />
                        <label
                            v-if="opportunity?.attachment_url"
                            class="mt-2 flex items-center gap-2 text-[0.95rem]"
                        >
                            <input
                                v-model="form.remove_attachment"
                                type="checkbox"
                            />
                            Remove the current document
                        </label>
                    </AdminField>

                    <AdminPublishedField v-model="form.is_published" />

                    <AdminFormActions
                        :submit-label="
                            isNew ? 'Add opportunity' : 'Save changes'
                        "
                        :cancel-href="index.url()"
                        :processing="form.processing"
                        :progress="form.progress"
                    />
                </form>
            </PortalPanel>
        </div>
    </div>
</template>
