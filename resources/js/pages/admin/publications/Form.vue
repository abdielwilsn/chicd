<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import { Spinner } from '@/components/ui/spinner';
import { index, store, update } from '@/routes/admin/publications';
import type { AdminPublication } from '@/types';

const props = defineProps<{
    publication: AdminPublication | null;
    categories: { value: string; label: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Publications', href: index() },
            { title: 'Publication', href: '#' },
        ],
    },
});

const isNew = computed(() => props.publication === null);

const form = useForm({
    title: props.publication?.title ?? '',
    category: props.publication?.category ?? '',
    year: props.publication?.year ?? new Date().getFullYear(),
    summary: props.publication?.summary ?? '',
    file: null as File | null,
    cover_image: null as File | null,
    remove_cover_image: false,
    is_published: props.publication?.is_published ?? true,
    is_featured: props.publication?.is_featured ?? false,
});

const isAnnualReport = computed(() => form.category === 'annual_report');

function pick(field: 'file' | 'cover_image', event: Event): void {
    form[field] = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submit(): void {
    if (props.publication) {
        // Files only travel in a POST, so the update is sent as a spoofed PUT.
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            update.url(props.publication.id),
        );

        return;
    }

    form.post(store.url());
}
</script>

<template>
    <Head :title="isNew ? 'Add a publication' : `Edit ${publication?.title}`" />

    <div>
        <PortalPageHeader
            eyebrow="Admin · Publications"
            :title="isNew ? 'Add a publication' : 'Edit publication'"
        />

        <div class="portal-container py-8 sm:py-10">
            <PortalPanel>
                <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
                    <div>
                        <label for="title" class="portal-label-text"
                            >Title</label
                        >
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            maxlength="200"
                            class="portal-input"
                            :aria-invalid="!!form.errors.title"
                        />
                        <InputError
                            :message="form.errors.title"
                            class="mt-1.5"
                        />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-[minmax(0,1fr)_10rem]">
                        <div>
                            <label for="category" class="portal-label-text"
                                >Type</label
                            >
                            <select
                                id="category"
                                v-model="form.category"
                                class="portal-input"
                                :aria-invalid="!!form.errors.category"
                            >
                                <option value="" disabled>Choose a type</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.value"
                                    :value="category.value"
                                >
                                    {{ category.label }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.category"
                                class="mt-1.5"
                            />
                        </div>
                        <div>
                            <label for="year" class="portal-label-text"
                                >Year</label
                            >
                            <input
                                id="year"
                                v-model.number="form.year"
                                type="number"
                                min="2000"
                                class="portal-input"
                                :aria-invalid="!!form.errors.year"
                            />
                            <InputError
                                :message="form.errors.year"
                                class="mt-1.5"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="summary" class="portal-label-text">
                            Summary
                            <span class="font-normal text-muted-foreground"
                                >(optional)</span
                            >
                        </label>
                        <textarea
                            id="summary"
                            v-model="form.summary"
                            rows="4"
                            maxlength="2000"
                            class="portal-input"
                        />
                        <InputError
                            :message="form.errors.summary"
                            class="mt-1.5"
                        />
                    </div>

                    <div>
                        <label for="file" class="portal-label-text">
                            Document
                            <span class="font-normal text-muted-foreground"
                                >(PDF, Word, Excel or PowerPoint, up to 20
                                MB)</span
                            >
                        </label>
                        <p
                            v-if="publication"
                            class="mb-2 text-[0.95rem] text-muted-foreground"
                        >
                            Current:
                            <a
                                :href="publication.file_url"
                                target="_blank"
                                rel="noopener"
                                class="font-semibold text-brand-700 hover:underline dark:text-brand-200"
                                >download</a
                            >
                            <template v-if="publication.file_size">
                                · {{ publication.file_size }}</template
                            >. Choose a file only to replace it.
                        </p>
                        <input
                            id="file"
                            type="file"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                            class="portal-input"
                            @change="pick('file', $event)"
                        />
                        <InputError
                            :message="form.errors.file"
                            class="mt-1.5"
                        />
                    </div>

                    <div>
                        <label for="cover" class="portal-label-text">
                            Cover image
                            <span class="font-normal text-muted-foreground"
                                >(optional, up to 4 MB)</span
                            >
                        </label>
                        <img
                            v-if="
                                publication?.cover_image_url &&
                                !form.remove_cover_image
                            "
                            :src="publication.cover_image_url"
                            alt=""
                            class="mb-2 h-32 w-auto border border-border object-cover"
                        />
                        <input
                            id="cover"
                            type="file"
                            accept="image/*"
                            class="portal-input"
                            @change="pick('cover_image', $event)"
                        />
                        <label
                            v-if="publication?.cover_image_url"
                            class="mt-2 flex items-center gap-2 text-[0.95rem]"
                        >
                            <input
                                v-model="form.remove_cover_image"
                                type="checkbox"
                            />
                            Remove the current cover image
                        </label>
                        <InputError
                            :message="form.errors.cover_image"
                            class="mt-1.5"
                        />
                    </div>

                    <fieldset class="space-y-3">
                        <legend class="portal-label-text">
                            On the website
                        </legend>
                        <label class="flex items-start gap-3">
                            <input
                                v-model="form.is_published"
                                type="checkbox"
                                class="mt-1"
                            />
                            <span>
                                <span class="font-semibold">Published</span>
                                <span
                                    class="block text-[0.95rem] text-muted-foreground"
                                    >Untick to keep it as a draft that visitors
                                    cannot see.</span
                                >
                            </span>
                        </label>
                        <label
                            v-if="isAnnualReport"
                            class="flex items-start gap-3"
                        >
                            <input
                                v-model="form.is_featured"
                                type="checkbox"
                                class="mt-1"
                            />
                            <span>
                                <span class="font-semibold"
                                    >Show as the featured annual report</span
                                >
                                <span
                                    class="block text-[0.95rem] text-muted-foreground"
                                    >Displayed with a download button on
                                    Publications and Policies & Accountability.
                                    Replaces any other featured report. If none
                                    is featured, the latest one is shown.</span
                                >
                            </span>
                        </label>
                    </fieldset>

                    <div class="flex flex-wrap gap-3">
                        <SiteButton type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            {{ isNew ? 'Add publication' : 'Save changes' }}
                        </SiteButton>
                        <SiteButton :href="index.url()" variant="outline"
                            >Cancel</SiteButton
                        >
                    </div>
                    <progress
                        v-if="form.progress"
                        :value="form.progress.percentage"
                        max="100"
                        class="w-full"
                    />
                </form>
            </PortalPanel>
        </div>
    </div>
</template>
