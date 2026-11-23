<script setup lang="ts">
import { Pause, Play } from '@lucide/vue';
import { computed } from 'vue';
import { useAutoRotate } from '@/composables/useAutoRotate';
import type { SiteImage } from '@/lib/siteContent';
import type { MediaItem } from '@/types';

type Props = {
    /** Gallery items chosen for the hero, in order. */
    items: MediaItem[];
    /** Shown when no gallery items have been chosen. */
    fallback: SiteImage;
};

const props = defineProps<Props>();

const { current, isPlaying, hasSeveral, goTo, togglePlaying } = useAutoRotate(
    computed(() => props.items.length),
);
</script>

<!-- The home page hero photograph, or the admin's chosen photos and videos taking turns. -->
<template>
    <div class="absolute inset-0">
        <img
            v-if="items.length === 0"
            :src="fallback.src"
            :alt="fallback.alt"
            class="absolute inset-0 size-full object-cover object-[50%_22%]"
            fetchpriority="high"
        />
        <template v-else>
            <div
                v-for="(item, index) in items"
                :key="item.id"
                class="absolute inset-0 transition-opacity duration-1000 motion-reduce:transition-none"
                :class="index === current ? 'opacity-100' : 'opacity-0'"
                :aria-hidden="index !== current"
            >
                <img
                    v-if="item.type === 'photo'"
                    :src="item.url"
                    :alt="item.alt"
                    class="size-full object-cover"
                    :fetchpriority="index === 0 ? 'high' : 'auto'"
                    :loading="index === 0 ? 'eager' : 'lazy'"
                />
                <video
                    v-else
                    :src="item.url"
                    :poster="item.poster_url ?? undefined"
                    :aria-label="item.alt || undefined"
                    class="size-full object-cover"
                    autoplay
                    muted
                    loop
                    playsinline
                />
            </div>

            <div
                v-if="hasSeveral"
                class="absolute right-4 bottom-4 flex items-center gap-3 bg-brand-950/70 px-3 py-2"
            >
                <button
                    type="button"
                    class="text-white hover:text-sun-300"
                    :aria-label="
                        isPlaying ? 'Pause the slideshow' : 'Play the slideshow'
                    "
                    @click="togglePlaying"
                >
                    <Pause v-if="isPlaying" class="size-4" />
                    <Play v-else class="size-4" />
                </button>
                <button
                    v-for="(item, index) in items"
                    :key="item.id"
                    type="button"
                    class="size-2.5 rounded-full transition-colors"
                    :class="
                        index === current
                            ? 'bg-sun-400'
                            : 'bg-white/60 hover:bg-white'
                    "
                    :aria-label="`Show item ${index + 1} of ${items.length}`"
                    :aria-current="index === current"
                    @click="goTo(index)"
                />
            </div>
        </template>
    </div>
</template>
