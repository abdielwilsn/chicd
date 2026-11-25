<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import type { SiteButtonVariants } from '@/components/site/siteButtonVariants';
import { siteButtonVariants } from '@/components/site/siteButtonVariants';
import { cn } from '@/lib/utils';

type Props = {
    href?: string;
    variant?: SiteButtonVariants['variant'];
    size?: SiteButtonVariants['size'];
    type?: 'button' | 'submit';
    disabled?: boolean;
    class?: HTMLAttributes['class'];
};

const props = withDefaults(defineProps<Props>(), {
    href: undefined,
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    class: undefined,
});

const classes = computed(() =>
    cn(
        siteButtonVariants({ variant: props.variant, size: props.size }),
        props.class,
    ),
);

/** Text links end with an orange chevron, in the manner of "Read more ›". */
const isTextLink = computed(
    () => props.variant === 'link' || props.variant === 'linkLight',
);

/** In-page anchors and external URLs bypass Inertia navigation. */
const isPlainLink = computed(
    () =>
        props.href !== undefined &&
        (props.href.startsWith('#') ||
            props.href.startsWith('http') ||
            props.href.startsWith('mailto:') ||
            props.href.startsWith('tel:')),
);
</script>

<template>
    <a v-if="href && isPlainLink" :href="href" :class="classes">
        <slot />
        <ChevronRight v-if="isTextLink" />
    </a>
    <Link v-else-if="href" :href="href" :class="classes">
        <slot />
        <ChevronRight v-if="isTextLink" />
    </Link>
    <button v-else :type="type" :disabled="disabled" :class="classes">
        <slot />
        <ChevronRight v-if="isTextLink" />
    </button>
</template>
