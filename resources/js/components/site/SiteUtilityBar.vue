<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Mail, MapPin } from '@lucide/vue';
import LanguagePicker from '@/components/site/LanguagePicker.vue';
import SocialLinks from '@/components/site/SocialLinks.vue';
import { login } from '@/routes';
import policies from '@/routes/policies';

const contact = usePage().props.site.contact;
</script>

<template>
    <div class="hidden bg-brand-950 text-[0.85rem] text-brand-100 md:block">
        <div
            class="site-container flex h-10 items-center justify-between gap-6"
        >
            <ul class="flex items-center gap-6">
                <li v-if="contact.address" class="flex items-center gap-2">
                    <MapPin class="size-3.5 text-sun-400" />
                    {{ contact.address }}
                </li>
                <li v-if="contact.email">
                    <a
                        :href="`mailto:${contact.email}`"
                        class="flex items-center gap-2 hover:text-white"
                    >
                        <Mail class="size-3.5 text-sun-400" />
                        {{ contact.email }}
                    </a>
                </li>
            </ul>
            <div class="flex items-center gap-5">
                <LanguagePicker inverted />
                <Link :href="policies.feedback()" class="hover:text-white"
                    >Report a concern</Link
                >
                <Link :href="login()" class="hover:text-white"
                    >Staff portal</Link
                >
                <SocialLinks
                    inverted
                    class="[&_a]:size-7 [&_a]:bg-transparent [&_svg]:size-4"
                />
            </div>
        </div>
    </div>
</template>
