<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Images, Play } from '@lucide/vue';
import { computed, ref } from 'vue';
import EmptyState from '@/components/site/EmptyState.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { siteImages } from '@/lib/siteContent';
import resources from '@/routes/resources';
import type { MediaItem } from '@/types';

defineProps<{
    items: MediaItem[];
}>();

const opened = ref<MediaItem | null>(null);

const isOpen = computed({
    get: () => opened.value !== null,
    set: (value: boolean) => {
        if (!value) {
            opened.value = null;
        }
    },
});
</script>

<template>
    <Head title="Media Gallery">
        <meta
            name="description"
            content="Photographs and videos from CHICD's programmes and community work."
        />
    </Head>

    <div>
        <PageHeader
            :image="siteImages.volunteer"
            eyebrow="Resources"
            title="Media Gallery"
            :breadcrumbs="[
                { title: 'Resources', href: resources.research.url() },
                { title: 'Media Gallery', href: resources.gallery.url() },
            ]"
        >
            <p>
                Photographs and videos from our programmes, community activities
                and events.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <ul
                    v-if="items.length"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <li v-for="item in items" :key="item.id">
                        <figure>
                            <button
                                type="button"
                                class="group relative block aspect-[4/3] w-full overflow-hidden bg-cream focus-visible:outline-4 focus-visible:outline-sun-400"
                                :aria-label="
                                    item.type === 'video'
                                        ? `Play video${item.caption ? `: ${item.caption}` : ''}`
                                        : `Enlarge photo${item.alt ? `: ${item.alt}` : ''}`
                                "
                                @click="opened = item"
                            >
                                <img
                                    v-if="item.type === 'photo'"
                                    :src="item.url"
                                    :alt="item.alt"
                                    loading="lazy"
                                    class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                                />
                                <template v-else>
                                    <img
                                        v-if="item.poster_url"
                                        :src="item.poster_url"
                                        alt=""
                                        loading="lazy"
                                        class="size-full object-cover"
                                    />
                                    <video
                                        v-else
                                        :src="`${item.url}#t=0.5`"
                                        preload="metadata"
                                        muted
                                        class="size-full object-cover"
                                        aria-hidden="true"
                                    />
                                    <span
                                        class="absolute inset-0 flex items-center justify-center bg-brand-950/30 group-hover:bg-brand-950/45"
                                        aria-hidden="true"
                                    >
                                        <span
                                            class="flex size-16 items-center justify-center rounded-full bg-sun-400 text-brand-950"
                                        >
                                            <Play class="ml-1 size-7" />
                                        </span>
                                    </span>
                                </template>
                            </button>
                            <figcaption
                                v-if="item.caption"
                                class="mt-2 text-[0.95rem] leading-snug text-stone-700"
                            >
                                {{ item.caption }}
                            </figcaption>
                        </figure>
                    </li>
                </ul>
                <EmptyState
                    v-else
                    :icon="Images"
                    title="Photographs and videos will appear here soon"
                >
                    <p>
                        We are gathering images from our programmes and
                        community activities, shared with the consent of the
                        people involved.
                    </p>
                </EmptyState>
            </div>
        </section>

        <Dialog v-model:open="isOpen">
            <DialogContent class="max-w-5xl bg-brand-950 p-3 text-white sm:p-4">
                <DialogTitle class="sr-only">
                    {{
                        opened?.caption ??
                        (opened?.type === 'video' ? 'Video' : 'Photograph')
                    }}
                </DialogTitle>
                <DialogDescription class="sr-only">
                    {{ opened?.alt || 'From the CHICD Media Gallery' }}
                </DialogDescription>
                <template v-if="opened">
                    <img
                        v-if="opened.type === 'photo'"
                        :src="opened.url"
                        :alt="opened.alt"
                        class="max-h-[80vh] w-full object-contain"
                    />
                    <video
                        v-else
                        :src="opened.url"
                        :poster="opened.poster_url ?? undefined"
                        controls
                        autoplay
                        playsinline
                        class="max-h-[80vh] w-full"
                    />
                    <p v-if="opened.caption" class="mt-3 text-brand-100">
                        {{ opened.caption }}
                    </p>
                </template>
            </DialogContent>
        </Dialog>
    </div>
</template>
