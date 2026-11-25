<script setup lang="ts">
import { Languages } from '@lucide/vue';
import { onMounted, ref, useId } from 'vue';
import {
    currentTranslationLanguage,
    translateSiteTo,
    translationLanguages,
} from '@/lib/googleTranslate';

type Props = {
    inverted?: boolean;
};

withDefaults(defineProps<Props>(), {
    inverted: false,
});

const id = useId();

/** English until mounted, so the server and browser render the same thing. */
const language = ref('en');

onMounted(() => {
    language.value = currentTranslationLanguage();
});

function choose(event: Event): void {
    translateSiteTo((event.target as HTMLSelectElement).value);
}
</script>

<!--
    Language names are shown in their own language and are never translated,
    so a visitor can always find theirs and get back to English.
-->
<template>
    <div class="notranslate flex items-center gap-2" translate="no">
        <Languages
            class="size-4 shrink-0"
            :class="inverted ? 'text-sun-400' : 'text-brand-700'"
            aria-hidden="true"
        />
        <label :for="id" class="sr-only">Translate this site</label>
        <select
            :id="id"
            :value="language"
            class="cursor-pointer rounded-[3px] border bg-transparent py-1 pr-7 pl-2 text-[0.85rem] focus:ring-2 focus:outline-none"
            :class="
                inverted
                    ? 'border-white/25 text-brand-100 hover:text-white focus:ring-white/60 [&>option]:text-ink'
                    : 'border-stone-300 text-ink focus:ring-brand-600/30'
            "
            title="Translated automatically by Google"
            @change="choose"
        >
            <option
                v-for="option in translationLanguages"
                :key="option.code"
                :value="option.code"
            >
                {{ option.name }}
            </option>
        </select>
    </div>
</template>
