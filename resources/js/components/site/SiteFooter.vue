<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Mail, MapPin, Phone } from '@lucide/vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SiteLogo from '@/components/site/SiteLogo.vue';
import SocialLinks from '@/components/site/SocialLinks.vue';
import {
    footerPolicyLinks,
    footerQuickLinks,
    programmes,
} from '@/lib/siteContent';
import { login } from '@/routes';
import policies from '@/routes/policies';

const page = usePage();
const contact = page.props.site.contact;
const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t-[6px] border-sun-400 bg-brand-950 text-brand-100">
        <div
            class="site-container grid grid-cols-2 gap-x-6 gap-y-12 py-16 lg:grid-cols-12 lg:gap-8"
        >
            <div class="col-span-2 lg:col-span-4">
                <SiteLogo inverted />
                <p class="mt-6 font-heading text-lg leading-snug text-white">
                    Centre for Health Innovation and Community Development
                    (CHICD)
                </p>
                <p class="mt-3 max-w-sm text-[0.98rem] leading-relaxed">
                    Advancing innovative solutions and inclusive community
                    development for healthier lives and sustainable
                    opportunities.
                </p>
                <ul class="mt-6 space-y-3 text-[0.98rem]">
                    <li v-if="contact.address" class="flex gap-3">
                        <MapPin class="mt-1 size-4 shrink-0 text-sun-400" />
                        <span>{{ contact.address }}</span>
                    </li>
                    <li v-if="contact.email" class="flex gap-3">
                        <Mail class="mt-1 size-4 shrink-0 text-sun-400" />
                        <a
                            :href="`mailto:${contact.email}`"
                            class="hover:text-white"
                            >{{ contact.email }}</a
                        >
                    </li>
                    <li v-if="contact.phone" class="flex gap-3">
                        <Phone class="mt-1 size-4 shrink-0 text-sun-400" />
                        <a
                            :href="`tel:${contact.phone}`"
                            class="hover:text-white"
                            >{{ contact.phone }}</a
                        >
                    </li>
                </ul>
                <SocialLinks inverted class="mt-6" />
            </div>

            <nav aria-label="Quick links" class="lg:col-span-2 lg:col-start-6">
                <h2
                    class="font-display text-lg font-bold tracking-[0.08em] text-white uppercase"
                >
                    Quick Links
                </h2>
                <ul class="mt-5 space-y-2.5 text-[0.98rem]">
                    <li v-for="link in footerQuickLinks" :key="link.title">
                        <Link :href="link.href" class="hover:text-white">{{
                            link.title
                        }}</Link>
                    </li>
                </ul>
            </nav>

            <nav aria-label="Programmes" class="lg:col-span-2">
                <h2
                    class="font-display text-lg font-bold tracking-[0.08em] text-white uppercase"
                >
                    Programmes
                </h2>
                <ul class="mt-5 space-y-2.5 text-[0.98rem]">
                    <li v-for="programme in programmes" :key="programme.slug">
                        <Link :href="programme.href" class="hover:text-white">{{
                            programme.name
                        }}</Link>
                    </li>
                </ul>
            </nav>

            <nav
                aria-label="Policies and accountability"
                class="col-span-2 lg:col-span-3"
            >
                <h2
                    class="font-display text-lg font-bold tracking-[0.08em] text-white uppercase"
                >
                    Policies &amp; Accountability
                </h2>
                <ul class="mt-5 space-y-2.5 text-[0.98rem]">
                    <li v-for="link in footerPolicyLinks" :key="link.title">
                        <Link :href="link.href" class="hover:text-white">{{
                            link.title
                        }}</Link>
                    </li>
                </ul>
                <SiteButton
                    :href="policies.feedback.url()"
                    variant="donate"
                    size="sm"
                    class="mt-6"
                >
                    Report a Concern
                </SiteButton>
            </nav>
        </div>

        <div class="border-t border-white/10">
            <div
                class="site-container flex flex-col gap-3 py-6 text-[0.88rem] text-brand-300 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    &copy; {{ year }} Centre for Health Innovation and Community
                    Development. All Rights Reserved.
                </p>
                <Link :href="login()" class="hover:text-white"
                    >Staff portal</Link
                >
            </div>
        </div>
    </footer>
</template>
