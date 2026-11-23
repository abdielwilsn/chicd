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
import { index, store, update } from '@/routes/admin/team';
import type { AdminTeamMember, Option } from '@/types';

const props = defineProps<{
    member: AdminTeamMember | null;
    sections: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Team page', href: index() },
            { title: 'Person', href: '#' },
        ],
    },
});

const isNew = computed(() => props.member === null);

const form = useForm({
    name: props.member?.name ?? '',
    position: props.member?.position ?? '',
    section: props.member?.section ?? 'team',
    bio: props.member?.bio ?? '',
    photo: null as File | null,
    remove_photo: false,
    sort_order: props.member?.sort_order ?? 0,
    is_published: props.member?.is_published ?? true,
});

function submit(): void {
    submitAdminForm(
        form,
        store.url(),
        props.member ? update.url(props.member.id) : null,
    );
}
</script>

<template>
    <Head :title="isNew ? 'Add a person' : `Edit ${member?.name}`" />

    <div>
        <PortalPageHeader
            eyebrow="Admin · Team page"
            :title="isNew ? 'Add a person' : 'Edit person'"
        />

        <div class="portal-container py-8 sm:py-10">
            <PortalPanel>
                <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <AdminField
                            id="name"
                            label="Name"
                            :error="form.errors.name"
                        >
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="120"
                                class="portal-input"
                                :aria-invalid="!!form.errors.name"
                            />
                        </AdminField>
                        <AdminField
                            id="position"
                            label="Role or job title"
                            :error="form.errors.position"
                        >
                            <input
                                id="position"
                                v-model="form.position"
                                type="text"
                                maxlength="150"
                                class="portal-input"
                                :aria-invalid="!!form.errors.position"
                            />
                        </AdminField>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-[minmax(0,1fr)_10rem]">
                        <AdminField
                            id="section"
                            label="Show under"
                            :error="form.errors.section"
                        >
                            <select
                                id="section"
                                v-model="form.section"
                                class="portal-input"
                            >
                                <option
                                    v-for="section in sections"
                                    :key="section.value"
                                    :value="section.value"
                                >
                                    {{ section.label }}
                                </option>
                            </select>
                        </AdminField>
                        <AdminField
                            id="sort_order"
                            label="Display order"
                            :error="form.errors.sort_order"
                        >
                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                max="999"
                                class="portal-input"
                            />
                        </AdminField>
                    </div>
                    <p class="-mt-4 text-[0.95rem] text-muted-foreground">
                        Lower numbers are shown first; people with the same
                        number are listed alphabetically.
                    </p>

                    <AdminField
                        id="bio"
                        label="Short biography"
                        note="(optional)"
                        :error="form.errors.bio"
                    >
                        <textarea
                            id="bio"
                            v-model="form.bio"
                            rows="5"
                            maxlength="1500"
                            class="portal-input"
                        />
                    </AdminField>

                    <AdminImageField
                        id="photo"
                        v-model="form.photo"
                        v-model:remove="form.remove_photo"
                        label="Photograph"
                        note="(optional, up to 4 MB). Without one, their initials are shown."
                        :current-url="member?.photo_url"
                        removable
                        :error="form.errors.photo"
                    />

                    <AdminPublishedField
                        v-model="form.is_published"
                        description="Untick to hide this person from the team page without deleting them."
                    />

                    <AdminFormActions
                        :submit-label="isNew ? 'Add person' : 'Save changes'"
                        :cancel-href="index.url()"
                        :processing="form.processing"
                        :progress="form.progress"
                    />
                </form>
            </PortalPanel>
        </div>
    </div>
</template>
