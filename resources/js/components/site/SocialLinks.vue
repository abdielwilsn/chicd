<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Props = {
    /** Use on dark backgrounds. */
    inverted?: boolean;
};

withDefaults(defineProps<Props>(), {
    inverted: false,
});

const page = usePage();

const labels: Record<string, string> = {
    facebook: 'Facebook',
    instagram: 'Instagram',
    linkedin: 'LinkedIn',
};

/** Simple brand marks; Lucide no longer ships brand icons. */
const paths: Record<string, string> = {
    facebook:
        'M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.8 1.4-3.8 3.9v2.3H8v3h2.5V21z',
    instagram:
        'M12 7.3A4.7 4.7 0 1 0 12 16.7 4.7 4.7 0 0 0 12 7.3zm0 7.7a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6-7.9a1.1 1.1 0 1 1-2.2 0 1.1 1.1 0 0 1 2.2 0zM12 4.6c2.4 0 2.7 0 3.6.1 2.4.1 3.6 1.3 3.7 3.7.1.9.1 1.2.1 3.6s0 2.7-.1 3.6c-.1 2.4-1.3 3.6-3.7 3.7-.9.1-1.2.1-3.6.1s-2.7 0-3.6-.1c-2.4-.1-3.6-1.3-3.7-3.7-.1-.9-.1-1.2-.1-3.6s0-2.7.1-3.6C4.8 6 6 4.8 8.4 4.7c.9-.1 1.2-.1 3.6-.1zM12 3c-2.4 0-2.8 0-3.7.1C5 3.2 3.2 5 3.1 8.3 3 9.2 3 9.6 3 12s0 2.8.1 3.7c.1 3.3 1.9 5.1 5.2 5.2.9.1 1.3.1 3.7.1s2.8 0 3.7-.1c3.3-.1 5.1-1.9 5.2-5.2.1-.9.1-1.3.1-3.7s0-2.8-.1-3.7C20.8 5 19 3.2 15.7 3.1 14.8 3 14.4 3 12 3z',
    linkedin:
        'M6.9 8.6H3.6V20h3.3zM5.3 3.5a1.9 1.9 0 1 0 0 3.8 1.9 1.9 0 0 0 0-3.8zM20.4 13.5c0-3-1.6-5.2-4.6-5.2-1.5 0-2.6.8-3 1.6V8.6H9.6V20h3.3v-5.7c0-1.5.3-3 2.2-3 1.8 0 1.9 1.7 1.9 3.1V20h3.4z',
};

const links = computed(() =>
    Object.entries(page.props.site.social).map(([network, url]) => ({
        network,
        url,
        label: labels[network] ?? network,
        path: paths[network],
    })),
);
</script>

<template>
    <ul v-if="links.length" class="flex items-center gap-2">
        <li v-for="link in links" :key="link.network">
            <a
                :href="link.url"
                target="_blank"
                rel="noopener noreferrer"
                class="flex size-10 items-center justify-center rounded-[3px] transition-colors"
                :class="
                    inverted
                        ? 'bg-white/10 text-white hover:bg-sun-400 hover:text-brand-950'
                        : 'bg-brand-900 text-white hover:bg-brand-700'
                "
            >
                <span class="sr-only">CHICD on {{ link.label }}</span>
                <svg
                    viewBox="0 0 24 24"
                    class="size-5 fill-current"
                    aria-hidden="true"
                >
                    <path :d="link.path" />
                </svg>
            </a>
        </li>
    </ul>
</template>
