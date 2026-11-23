<script setup lang="ts">
import { ChevronLeft, ChevronRight, Pause, Play } from '@lucide/vue';
import { computed } from 'vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { useAutoRotate } from '@/composables/useAutoRotate';
import type { ThematicArea } from '@/lib/siteContent';
import work from '@/routes/work';

type Props = {
    areas: ThematicArea[];
};

const props = defineProps<Props>();

const {
    current,
    isPlaying,
    hasSeveral,
    goTo,
    next,
    previous,
    togglePlaying,
    hold,
} = useAutoRotate(computed(() => props.areas.length));

const total = computed(() => String(props.areas.length).padStart(2, '0'));

const areaHref = (area: ThematicArea): string =>
    `${work.index.url()}#${area.slug}`;

/** Hold the slide while focus is anywhere inside the carousel. */
function onFocusOut(event: FocusEvent): void {
    const carousel = event.currentTarget as HTMLElement;

    if (!carousel.contains(event.relatedTarget as Node | null)) {
        hold(false);
    }
}
</script>

<!--
    The home page hero: one full-width slide per thematic area, taking turns.
    CHICD's name is the page's main heading and each area title is a slide
    heading. Hidden slides are inert, so only the visible slide's link can be
    reached with the keyboard. Rotation pauses on hover and focus, can be
    paused outright, and is off for visitors who prefer reduced motion.
-->
<template>
    <section
        class="relative isolate overflow-hidden bg-brand-950 text-white"
        aria-roledescription="carousel"
        aria-label="Our thematic areas"
        @mouseenter="hold(true)"
        @mouseleave="hold(false)"
        @focusin="hold(true)"
        @focusout="onFocusOut"
    >
        <!-- Photographs, cross-fading behind the text. -->
        <div
            v-for="(area, index) in areas"
            :key="`image-${area.slug}`"
            class="absolute inset-0 -z-10 transition-opacity duration-1000 motion-reduce:transition-none"
            :class="index === current ? 'opacity-100' : 'opacity-0'"
            aria-hidden="true"
        >
            <img
                :src="area.image.src"
                alt=""
                class="size-full object-cover"
                :fetchpriority="index === 0 ? 'high' : 'auto'"
                :loading="index === 0 ? 'eager' : 'lazy'"
            />
        </div>
        <div
            class="absolute inset-0 -z-10 bg-brand-950/75 sm:bg-transparent sm:bg-gradient-to-r sm:from-brand-950/95 sm:via-brand-950/75 sm:to-brand-950/10"
            aria-hidden="true"
        />

        <div
            class="site-container flex min-h-[34rem] flex-col justify-center pt-14 pb-28 sm:min-h-[38rem] lg:min-h-[42rem]"
        >
            <h1
                class="font-display text-[0.95rem] font-bold tracking-[0.14em] text-sun-300 uppercase sm:text-[1rem]"
            >
                Centre for Health Innovation and Community Development
            </h1>

            <!-- Every slide sits in the same grid cell, so the hero is as tall as the longest one. -->
            <div class="mt-5 grid max-w-3xl">
                <div
                    v-for="(area, index) in areas"
                    :key="area.slug"
                    role="group"
                    aria-roledescription="slide"
                    :aria-label="`${index + 1} of ${areas.length}: ${area.title}`"
                    class="transition-opacity duration-700 [grid-area:1/1] motion-reduce:transition-none"
                    :class="
                        index === current
                            ? 'opacity-100'
                            : 'pointer-events-none opacity-0'
                    "
                    :aria-hidden="index !== current"
                    :inert="index !== current"
                >
                    <p class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center px-2.5 py-1 font-display text-[0.95rem] leading-none font-bold"
                            :class="area.tone.tile"
                            >0{{ area.number }} / {{ total }}</span
                        >
                        <span
                            class="font-display text-[0.95rem] font-bold tracking-[0.12em] text-brand-100 uppercase"
                            >Thematic area</span
                        >
                    </p>
                    <h2
                        class="mt-4 font-display text-[2.4rem] leading-[0.95] font-extrabold uppercase sm:text-[3.4rem] xl:text-[4rem]"
                    >
                        {{ area.title }}
                    </h2>
                    <p
                        class="mt-6 max-w-2xl text-[1.0625rem] leading-relaxed text-brand-100 sm:text-[1.15rem]"
                    >
                        {{ area.summary }}
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <SiteButton
                            :href="areaHref(area)"
                            variant="donate"
                            size="lg"
                        >
                            Explore this area
                        </SiteButton>
                        <SiteButton
                            :href="work.index.url()"
                            variant="ghostLight"
                            size="lg"
                        >
                            All of our work
                        </SiteButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div
            v-if="hasSeveral"
            class="absolute inset-x-0 bottom-0 border-t border-white/15 bg-brand-950/60 backdrop-blur-sm"
        >
            <div
                class="site-container flex items-center justify-between gap-4 py-3"
            >
                <div
                    class="flex items-center gap-2"
                    role="group"
                    aria-label="Choose a thematic area"
                >
                    <button
                        v-for="(area, index) in areas"
                        :key="`dot-${area.slug}`"
                        type="button"
                        class="h-1.5 rounded-full transition-all focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        :class="
                            index === current
                                ? 'w-8 bg-sun-400'
                                : 'w-4 bg-white/50 hover:bg-white'
                        "
                        :aria-label="`Show ${area.title}`"
                        :aria-current="index === current"
                        @click="goTo(index)"
                    />
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="grid size-10 place-items-center text-white hover:text-sun-300 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        aria-label="Previous thematic area"
                        @click="previous"
                    >
                        <ChevronLeft class="size-5" />
                    </button>
                    <button
                        type="button"
                        class="grid size-10 place-items-center text-white hover:text-sun-300 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        :aria-label="
                            isPlaying
                                ? 'Pause the slideshow'
                                : 'Play the slideshow'
                        "
                        @click="togglePlaying"
                    >
                        <Pause v-if="isPlaying" class="size-4" />
                        <Play v-else class="size-4" />
                    </button>
                    <button
                        type="button"
                        class="grid size-10 place-items-center text-white hover:text-sun-300 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        aria-label="Next thematic area"
                        @click="next"
                    >
                        <ChevronRight class="size-5" />
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
