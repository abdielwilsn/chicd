<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Images, ImagePlus, Star, Video } from '@lucide/vue';
import AdminRowActions from '@/components/admin/AdminRowActions.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import { create, destroy, edit, index } from '@/routes/admin/gallery';
import type { AdminMediaItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Media gallery', href: index() }],
    },
});

defineProps<{
    items: AdminMediaItem[];
}>();
</script>

<template>
    <Head title="Media gallery" />

    <div>
        <PortalPageHeader eyebrow="Admin · Website" title="Media gallery">
            <p>
                Photographs and videos in the website's Media Gallery. Items
                marked for the hero rotate at the top of the home page with
                CHICD's headline. With none marked, the home page shows the
                thematic areas carousel instead.
            </p>
            <template #actions>
                <SiteButton :href="create.url()" variant="donate">
                    <ImagePlus />
                    Add a photo or video
                </SiteButton>
            </template>
        </PortalPageHeader>

        <div class="portal-container py-8 sm:py-10">
            <ul
                v-if="items.length"
                class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="flex flex-col border border-border bg-card"
                >
                    <div class="relative aspect-[4/3] bg-secondary">
                        <img
                            v-if="item.type === 'photo'"
                            :src="item.url"
                            :alt="item.alt"
                            class="size-full object-cover"
                            loading="lazy"
                        />
                        <video
                            v-else
                            :src="item.url"
                            :poster="item.poster_url ?? undefined"
                            preload="metadata"
                            muted
                            class="size-full object-cover"
                        />
                        <span
                            v-if="item.type === 'video'"
                            class="absolute top-2 left-2 inline-flex items-center gap-1 rounded-[3px] bg-brand-950/80 px-2 py-0.5 text-[0.8rem] font-bold text-white"
                        >
                            <Video class="size-3.5" /> Video
                        </span>
                        <span
                            v-if="item.show_in_hero"
                            class="absolute top-2 right-2 inline-flex items-center gap-1 rounded-[3px] bg-sun-400 px-2 py-0.5 text-[0.8rem] font-bold text-brand-950"
                        >
                            <Star class="size-3.5" /> Home hero
                        </span>
                    </div>
                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <p class="flex-1 text-[0.95rem]">
                            {{ item.caption ?? item.alt_text ?? 'No caption' }}
                        </p>
                        <p class="text-[0.85rem] text-muted-foreground">
                            Order {{ item.sort_order
                            }}<template v-if="item.file_size">
                                · {{ item.file_size }}</template
                            >
                        </p>
                        <AdminRowActions
                            :edit-href="edit.url(item.id)"
                            :destroy-url="destroy.url(item.id)"
                            :confirm-message="`Remove this ${item.type} from the website? The file will be deleted.`"
                        />
                    </div>
                </li>
            </ul>

            <PortalEmptyState
                v-else
                :icon="Images"
                title="The gallery is empty"
            >
                Add photographs and videos from CHICD's work to build the Media
                Gallery and choose what appears on the home page.
            </PortalEmptyState>
        </div>
    </div>
</template>
