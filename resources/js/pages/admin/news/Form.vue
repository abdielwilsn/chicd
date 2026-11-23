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
import { index, store, update } from '@/routes/admin/news';
import type { AdminPost, Option } from '@/types';

const props = defineProps<{
    post: AdminPost | null;
    types: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'News & events', href: index() },
            { title: 'Post', href: '#' },
        ],
    },
});

const isNew = computed(() => props.post === null);

const form = useForm({
    title: props.post?.title ?? '',
    type: props.post?.type ?? 'news',
    excerpt: props.post?.excerpt_text ?? '',
    body: props.post?.body_text ?? '',
    cover_image: null as File | null,
    cover_image_alt: props.post?.cover_image_alt ?? '',
    remove_cover_image: false,
    event_starts_at: props.post?.event_starts_at ?? '',
    event_location: props.post?.event_location ?? '',
    is_published: props.post?.is_published ?? true,
});

const isEvent = computed(() => form.type === 'event');

function submit(): void {
    submitAdminForm(
        form,
        store.url(),
        props.post ? update.url(props.post.id) : null,
    );
}
</script>

<template>
    <Head :title="isNew ? 'Write a post' : `Edit ${post?.title}`" />

    <div>
        <PortalPageHeader
            eyebrow="Admin · News & events"
            :title="isNew ? 'Write a post' : 'Edit post'"
        />

        <div class="portal-container py-8 sm:py-10">
            <PortalPanel>
                <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
                    <div class="grid gap-6 sm:grid-cols-[minmax(0,1fr)_12rem]">
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
                        <AdminField
                            id="type"
                            label="Type"
                            :error="form.errors.type"
                        >
                            <select
                                id="type"
                                v-model="form.type"
                                class="portal-input"
                            >
                                <option
                                    v-for="type in types"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </AdminField>
                    </div>

                    <div
                        v-if="isEvent"
                        class="grid gap-6 border-l-4 border-sun-400 pl-4 sm:grid-cols-[14rem_minmax(0,1fr)]"
                    >
                        <AdminField
                            id="event_starts_at"
                            label="Starts"
                            note="(Nigeria time)"
                            :error="form.errors.event_starts_at"
                        >
                            <input
                                id="event_starts_at"
                                v-model="form.event_starts_at"
                                type="datetime-local"
                                class="portal-input"
                                :aria-invalid="!!form.errors.event_starts_at"
                            />
                        </AdminField>
                        <AdminField
                            id="event_location"
                            label="Venue"
                            note="(optional)"
                            :error="form.errors.event_location"
                        >
                            <input
                                id="event_location"
                                v-model="form.event_location"
                                type="text"
                                maxlength="200"
                                class="portal-input"
                            />
                        </AdminField>
                    </div>

                    <AdminField
                        id="excerpt"
                        label="Introduction"
                        note="(optional)"
                        hint="A sentence or two shown in listings. Without one, the start of the post is used."
                        :error="form.errors.excerpt"
                    >
                        <textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            rows="2"
                            maxlength="500"
                            class="portal-input"
                        />
                    </AdminField>

                    <AdminField
                        id="body"
                        label="Post"
                        hint="Leave a blank line between paragraphs."
                        :error="form.errors.body"
                    >
                        <textarea
                            id="body"
                            v-model="form.body"
                            rows="14"
                            maxlength="50000"
                            class="portal-input"
                            :aria-invalid="!!form.errors.body"
                        />
                    </AdminField>

                    <AdminImageField
                        id="cover_image"
                        v-model="form.cover_image"
                        v-model:remove="form.remove_cover_image"
                        label="Cover image"
                        note="(optional, up to 4 MB)"
                        :current-url="post?.cover_image?.src"
                        removable
                        :error="form.errors.cover_image"
                    />

                    <AdminField
                        id="cover_image_alt"
                        label="Cover image description"
                        note="(recommended)"
                        hint="Describe the image for people using screen readers."
                        :error="form.errors.cover_image_alt"
                    >
                        <input
                            id="cover_image_alt"
                            v-model="form.cover_image_alt"
                            type="text"
                            maxlength="200"
                            class="portal-input"
                        />
                    </AdminField>

                    <AdminPublishedField v-model="form.is_published" />

                    <AdminFormActions
                        :submit-label="isNew ? 'Add post' : 'Save changes'"
                        :cancel-href="index.url()"
                        :processing="form.processing"
                        :progress="form.progress"
                    />
                </form>
            </PortalPanel>
        </div>
    </div>
</template>
