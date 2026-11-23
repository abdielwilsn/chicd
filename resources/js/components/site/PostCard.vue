<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarDays, ChevronRight } from '@lucide/vue';
import type { Post } from '@/types';

type Props = {
    post: Post;
};

defineProps<Props>();
</script>

<!-- A story, news article, event or announcement in a listing. -->
<template>
    <Link
        :href="post.url"
        class="group flex h-full flex-col border-t-[5px] border-sun-400 bg-cream/60 transition-colors hover:bg-cream"
    >
        <div v-if="post.cover_image" class="aspect-[16/10] overflow-hidden">
            <img
                :src="post.cover_image.src"
                :alt="post.cover_image.alt"
                loading="lazy"
                class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
            />
        </div>
        <div class="flex flex-1 flex-col p-6">
            <p
                class="mb-2 font-display text-[0.9rem] font-bold tracking-[0.12em] text-brand-700 uppercase"
            >
                {{ post.type_label }}
                <span
                    v-if="post.published_on"
                    class="font-sans font-normal tracking-normal text-stone-600 normal-case"
                >
                    · {{ post.published_on }}</span
                >
            </p>
            <h3 class="font-heading text-xl leading-snug font-bold text-ink">
                {{ post.title }}
            </h3>
            <p
                v-if="post.event_starts"
                class="mt-2 flex items-start gap-2 text-[0.95rem] font-semibold text-brand-800"
            >
                <CalendarDays class="mt-0.5 size-4 shrink-0 text-sun-600" />
                {{ post.event_starts
                }}<template v-if="post.event_location">
                    · {{ post.event_location }}</template
                >
            </p>
            <p class="mt-2 flex-1 leading-relaxed text-stone-700">
                {{ post.excerpt }}
            </p>
            <span
                class="mt-5 inline-flex items-center gap-1 font-bold text-brand-700 group-hover:underline"
            >
                Read more
                <ChevronRight class="size-4 text-sun-500" />
            </span>
        </div>
    </Link>
</template>
