<script setup lang="ts">
type Props = {
    items: string[];
    columns?: 1 | 2;
    inverted?: boolean;
};

withDefaults(defineProps<Props>(), {
    columns: 1,
    inverted: false,
});
</script>

<template>
    <ul
        :class="
            columns === 2 ? 'grid gap-x-8 gap-y-3 sm:grid-cols-2' : 'space-y-3'
        "
    >
        <li
            v-for="item in items"
            :key="item"
            class="flex gap-3 leading-snug"
            :class="inverted ? 'text-white' : 'text-ink'"
        >
            <svg
                viewBox="0 0 12 12"
                class="mt-[0.28em] size-3 shrink-0"
                :class="inverted ? 'fill-sun-400' : 'fill-sun-500'"
                aria-hidden="true"
            >
                <path d="M4.5 0h3v4.5H12v3H7.5V12h-3V7.5H0v-3h4.5z" />
            </svg>
            <span>{{ item }}</span>
        </li>
    </ul>
</template>
