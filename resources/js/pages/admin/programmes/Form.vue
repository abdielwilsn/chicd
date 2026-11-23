<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminField from '@/components/admin/AdminField.vue';
import AdminFormActions from '@/components/admin/AdminFormActions.vue';
import AdminImageField from '@/components/admin/AdminImageField.vue';
import AdminPublishedField from '@/components/admin/AdminPublishedField.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import { submitAdminForm } from '@/lib/adminForm';
import { index, store, update } from '@/routes/admin/programmes';
import type { AdminProgramme } from '@/types';

const props = defineProps<{
    programme: AdminProgramme | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Programmes', href: index() },
            { title: 'Programme', href: '#' },
        ],
    },
});

const isNew = computed(() => props.programme === null);

const form = useForm({
    name: props.programme?.name ?? '',
    tagline: props.programme?.tagline ?? '',
    summary: props.programme?.summary_text ?? '',
    body: props.programme?.body_text ?? '',
    image: null as File | null,
    image_alt: props.programme?.image_alt ?? '',
    sort_order: props.programme?.sort_order ?? 0,
    is_published: props.programme?.is_published ?? true,
});

function submit(): void {
    submitAdminForm(
        form,
        store.url(),
        props.programme ? update.url(props.programme.id) : null,
    );
}
</script>

<template>
    <Head :title="isNew ? 'Add a programme' : `Edit ${programme?.name}`" />

    <div>
        <PortalPageHeader
            eyebrow="Admin · Programmes"
            :title="isNew ? 'Add a programme' : 'Edit programme'"
        />

        <div class="portal-container py-8 sm:py-10">
            <PortalPanel>
                <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
                    <AdminField
                        id="name"
                        label="Name"
                        :error="form.errors.name"
                    >
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            maxlength="150"
                            class="portal-input"
                            :aria-invalid="!!form.errors.name"
                        />
                    </AdminField>

                    <AdminField
                        id="tagline"
                        label="Tagline"
                        note="(optional)"
                        hint="A short line shown above the name, e.g. “Supporting Educational Participation and Dignity”."
                        :error="form.errors.tagline"
                    >
                        <input
                            id="tagline"
                            v-model="form.tagline"
                            type="text"
                            maxlength="200"
                            class="portal-input"
                        />
                    </AdminField>

                    <AdminField
                        id="summary"
                        label="Summary"
                        hint="Shown on the home page and Our Work. Leave a blank line between paragraphs."
                        :error="form.errors.summary"
                    >
                        <textarea
                            id="summary"
                            v-model="form.summary"
                            rows="4"
                            maxlength="2000"
                            class="portal-input"
                            :aria-invalid="!!form.errors.summary"
                        />
                    </AdminField>

                    <AdminField
                        id="body"
                        label="Full description"
                        note="(optional)"
                        hint="Shown on the programme's own page, below the summary. Leave a blank line between paragraphs."
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

                    <AdminImageField
                        id="image"
                        v-model="form.image"
                        label="Photograph"
                        :current-url="programme?.image.src"
                        :error="form.errors.image"
                    />

                    <AdminField
                        id="image_alt"
                        label="Photograph description"
                        note="(recommended)"
                        hint="Describe the photograph for people using screen readers."
                        :error="form.errors.image_alt"
                    >
                        <input
                            id="image_alt"
                            v-model="form.image_alt"
                            type="text"
                            maxlength="200"
                            class="portal-input"
                        />
                    </AdminField>

                    <AdminField
                        id="sort_order"
                        label="Display order"
                        hint="Lower numbers are shown first."
                        :error="form.errors.sort_order"
                    >
                        <input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            max="999"
                            class="portal-input max-w-40"
                        />
                    </AdminField>

                    <AdminPublishedField v-model="form.is_published" />

                    <AdminFormActions
                        :submit-label="isNew ? 'Add programme' : 'Save changes'"
                        :cancel-href="index.url()"
                        :processing="form.processing"
                        :progress="form.progress"
                    />
                </form>
            </PortalPanel>
        </div>
    </div>
</template>
