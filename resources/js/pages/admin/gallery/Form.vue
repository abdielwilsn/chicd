<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminField from '@/components/admin/AdminField.vue';
import AdminFormActions from '@/components/admin/AdminFormActions.vue';
import AdminImageField from '@/components/admin/AdminImageField.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import { index, store, update } from '@/routes/admin/gallery';
import type { AdminMediaItem } from '@/types';

const props = defineProps<{
    item: AdminMediaItem | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Media gallery', href: index() },
            { title: 'Item', href: '#' },
        ],
    },
});

const isNew = computed(() => props.item === null);

const form = useForm({
    type: props.item?.type ?? 'photo',
    file: null as File | null,
    poster: null as File | null,
    caption: props.item?.caption ?? '',
    alt_text: props.item?.alt_text ?? '',
    show_in_hero: props.item?.show_in_hero ?? false,
    sort_order: props.item?.sort_order ?? 0,
});

const isVideo = computed(() => form.type === 'video');

function pickFile(event: Event): void {
    form.file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submit(): void {
    if (props.item) {
        // The type and file are fixed once an item has been added. Files only
        // travel in a POST, so the update is sent as a spoofed PUT.
        form.transform((data) => ({
            caption: data.caption,
            alt_text: data.alt_text,
            show_in_hero: data.show_in_hero,
            sort_order: data.sort_order,
            poster: data.poster,
            _method: 'put',
        })).post(update.url(props.item.id));

        return;
    }

    form.transform((data) => ({
        ...data,
        poster: isVideo.value ? data.poster : null,
    })).post(store.url());
}
</script>

<template>
    <Head :title="isNew ? 'Add a photo or video' : 'Edit gallery item'" />

    <div>
        <PortalPageHeader
            eyebrow="Admin · Media gallery"
            :title="isNew ? 'Add a photo or video' : 'Edit gallery item'"
        />

        <div class="portal-container py-8 sm:py-10">
            <PortalPanel>
                <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
                    <fieldset v-if="isNew" class="space-y-2">
                        <legend class="portal-label-text">Type</legend>
                        <div class="flex flex-wrap gap-6">
                            <label class="flex items-center gap-2">
                                <input
                                    v-model="form.type"
                                    type="radio"
                                    value="photo"
                                />
                                Photograph
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    v-model="form.type"
                                    type="radio"
                                    value="video"
                                />
                                Video
                            </label>
                        </div>
                    </fieldset>

                    <AdminField
                        v-if="isNew"
                        id="file"
                        :label="isVideo ? 'Video file' : 'Photograph'"
                        :note="
                            isVideo
                                ? '(MP4 or WebM, up to 50 MB)'
                                : '(JPG, PNG or WebP, up to 8 MB)'
                        "
                        :error="form.errors.file"
                    >
                        <input
                            id="file"
                            :key="form.type"
                            type="file"
                            :accept="
                                isVideo ? 'video/mp4,video/webm' : 'image/*'
                            "
                            class="portal-input"
                            :aria-invalid="!!form.errors.file"
                            @change="pickFile"
                        />
                    </AdminField>
                    <div v-else class="max-w-md">
                        <img
                            v-if="item?.type === 'photo'"
                            :src="item.url"
                            :alt="item.alt"
                            class="border border-border"
                        />
                        <video
                            v-else-if="item"
                            :src="item.url"
                            :poster="item.poster_url ?? undefined"
                            controls
                            preload="metadata"
                            class="w-full border border-border"
                        />
                        <p class="mt-2 text-[0.95rem] text-muted-foreground">
                            To use a different file, remove this item and add
                            the new one.
                        </p>
                    </div>

                    <AdminImageField
                        v-if="isVideo"
                        id="poster"
                        v-model="form.poster"
                        label="Poster image"
                        note="(optional, up to 4 MB). Shown before the video plays."
                        :current-url="item?.poster_url"
                        :error="form.errors.poster"
                    />

                    <AdminField
                        id="alt_text"
                        :label="
                            isVideo ? 'Description' : 'Photograph description'
                        "
                        :note="isVideo ? '(optional)' : undefined"
                        hint="Describe what it shows for people using screen readers."
                        :error="form.errors.alt_text"
                    >
                        <input
                            id="alt_text"
                            v-model="form.alt_text"
                            type="text"
                            maxlength="200"
                            class="portal-input"
                            :aria-invalid="!!form.errors.alt_text"
                        />
                    </AdminField>

                    <AdminField
                        id="caption"
                        label="Caption"
                        note="(optional)"
                        hint="Shown beneath it in the gallery."
                        :error="form.errors.caption"
                    >
                        <input
                            id="caption"
                            v-model="form.caption"
                            type="text"
                            maxlength="200"
                            class="portal-input"
                        />
                    </AdminField>

                    <AdminField
                        id="sort_order"
                        label="Display order"
                        hint="Lower numbers are shown first, in the gallery and the hero."
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

                    <label class="flex items-start gap-3">
                        <input
                            v-model="form.show_in_hero"
                            type="checkbox"
                            class="mt-1"
                        />
                        <span>
                            <span class="font-semibold"
                                >Show in the home page hero</span
                            >
                            <span
                                class="block text-[0.95rem] text-muted-foreground"
                                >Hero items take turns at the top of the home
                                page instead of the thematic areas. Landscape
                                photographs and short, silent videos work
                                best.</span
                            >
                        </span>
                    </label>

                    <AdminFormActions
                        :submit-label="
                            isNew ? 'Add to gallery' : 'Save changes'
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
