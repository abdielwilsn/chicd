<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { SiteImage, SiteNavLink } from '@/lib/siteContent';
import { home } from '@/routes';

type Props = {
    eyebrow?: string;
    title: string;
    breadcrumbs?: SiteNavLink[];
    /** Full-width photograph shown above the title panel. */
    image?: SiteImage;
};

withDefaults(defineProps<Props>(), {
    eyebrow: undefined,
    breadcrumbs: () => [],
    image: undefined,
});
</script>

<template>
    <section
        :class="
            image ? 'bg-white' : 'border-b-[6px] border-sun-400 bg-brand-900'
        "
    >
        <div v-if="image" class="relative h-60 sm:h-80 lg:h-[27rem]">
            <img
                :src="image.src"
                alt=""
                class="absolute inset-0 size-full object-cover"
                fetchpriority="high"
            />
        </div>
        <div
            class="site-container"
            :class="image ? 'relative -mt-16 sm:-mt-28 lg:-mt-40' : ''"
        >
            <div
                class="text-white"
                :class="
                    image
                        ? 'max-w-3xl border-t-[6px] border-sun-400 bg-brand-900 px-5 py-8 sm:px-10 sm:py-10 lg:px-12 lg:py-12'
                        : 'max-w-4xl py-14 sm:py-20'
                "
            >
                <nav
                    v-if="breadcrumbs.length"
                    aria-label="Breadcrumb"
                    class="mb-6 text-[0.9rem] text-brand-200"
                >
                    <ol class="flex flex-wrap items-center gap-2">
                        <li>
                            <Link :href="home()" class="hover:text-white"
                                >Home</Link
                            >
                        </li>
                        <li
                            v-for="crumb in breadcrumbs"
                            :key="crumb.href"
                            class="flex items-center gap-2"
                        >
                            <span class="text-sun-400" aria-hidden="true"
                                >/</span
                            >
                            <Link :href="crumb.href" class="hover:text-white">{{
                                crumb.title
                            }}</Link>
                        </li>
                    </ol>
                </nav>
                <p
                    v-if="eyebrow"
                    class="mb-3 font-display text-[1rem] font-bold tracking-[0.14em] text-sun-300 uppercase"
                >
                    {{ eyebrow }}
                </p>
                <h1
                    class="font-display text-[2.3rem] leading-[0.95] font-extrabold tracking-[0.005em] uppercase sm:text-[3.5rem] lg:text-[4.1rem]"
                >
                    {{ title }}
                </h1>
                <div
                    v-if="$slots.default"
                    class="mt-6 space-y-4 text-[1.0625rem] leading-relaxed text-brand-100 sm:text-lg"
                >
                    <slot />
                </div>
                <div
                    v-if="$slots.actions"
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap"
                >
                    <slot name="actions" />
                </div>
            </div>
        </div>
    </section>
</template>
