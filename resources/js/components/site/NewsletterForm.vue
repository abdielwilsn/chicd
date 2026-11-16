<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { CircleCheck } from '@lucide/vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { store } from '@/routes/newsletter';

type Props = {
    /** Use on dark backgrounds. */
    inverted?: boolean;
};

withDefaults(defineProps<Props>(), {
    inverted: false,
});
</script>

<template>
    <Form
        v-bind="store.form()"
        reset-on-success
        :options="{ preserveScroll: true }"
        #default="{ errors, processing, wasSuccessful }"
    >
        <div
            class="absolute -left-[10000px] h-px w-px overflow-hidden"
            aria-hidden="true"
        >
            <input
                type="text"
                name="website"
                tabindex="-1"
                autocomplete="off"
            />
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <label for="newsletter-email" class="sr-only">Email address</label>
            <input
                id="newsletter-email"
                name="email"
                type="email"
                required
                autocomplete="email"
                placeholder="Your email address"
                class="h-14 w-full rounded-[3px] border-2 px-5 font-body text-[1.05rem] focus:outline-none sm:flex-1"
                :class="
                    inverted
                        ? 'border-white/25 bg-white/10 text-white placeholder:text-brand-200 focus:border-sun-400'
                        : 'border-brand-950/20 bg-white text-ink placeholder:text-stone-400 focus:border-brand-700'
                "
                :aria-invalid="!!errors.email"
            />
            <SiteButton
                type="submit"
                size="lg"
                :variant="inverted ? 'donate' : 'primary'"
                :disabled="processing"
            >
                Subscribe to Updates
            </SiteButton>
        </div>
        <p
            v-if="errors.email"
            class="mt-2 text-[0.98rem] font-semibold"
            :class="inverted ? 'text-sun-200' : 'text-terracotta-700'"
        >
            {{ errors.email }}
        </p>
        <p
            v-else-if="wasSuccessful"
            class="mt-3 flex items-center gap-2 text-[0.98rem] font-semibold"
            :class="inverted ? 'text-white' : 'text-brand-900'"
            role="status"
        >
            <CircleCheck class="size-5" />
            Thank you for subscribing. We will keep you informed.
        </p>
    </Form>
</template>
