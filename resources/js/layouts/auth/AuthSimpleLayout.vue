<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import SiteLogo from '@/components/site/SiteLogo.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-svh bg-background font-body text-foreground">
        <header
            class="border-b-[6px] border-sun-400 bg-brand-900 pt-6 pb-28 text-white sm:pt-8"
        >
            <div
                class="mx-auto flex w-full max-w-md items-center justify-between gap-4 px-5"
            >
                <Link :href="home()" aria-label="CHICD home">
                    <SiteLogo inverted :show-tagline="false" />
                </Link>
                <Link
                    :href="home()"
                    class="flex items-center gap-1.5 text-[0.95rem] text-brand-100 hover:text-white"
                >
                    <ArrowLeft class="size-4 text-sun-400" />
                    Website
                </Link>
            </div>
            <div class="mx-auto mt-10 w-full max-w-md px-5">
                <p
                    class="mb-2 font-display text-[0.95rem] font-bold tracking-[0.14em] text-sun-300 uppercase"
                >
                    Staff portal
                </p>
                <h1
                    class="font-display text-[2.2rem] leading-[0.95] font-extrabold uppercase sm:text-[2.6rem]"
                >
                    {{ title }}
                </h1>
            </div>
        </header>

        <main class="mx-auto -mt-20 w-full max-w-md px-5 pb-12">
            <div
                class="border-t-[6px] border-sun-400 bg-card px-5 py-7 shadow-[0_20px_40px_-24px_rgb(10_36_25/0.35)] ring-1 ring-border sm:px-8 sm:py-8"
            >
                <p
                    v-if="description"
                    class="mb-6 leading-relaxed text-muted-foreground"
                >
                    {{ description }}
                </p>
                <slot />
            </div>
        </main>
    </div>
</template>
