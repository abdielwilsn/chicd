<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Mail, MapPin, Phone } from '@lucide/vue';
import { computed } from 'vue';
import EnquiryForm from '@/components/site/EnquiryForm.vue';
import FormPanel from '@/components/site/FormPanel.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SocialLinks from '@/components/site/SocialLinks.vue';
import { contact as contactRoute } from '@/routes';

const page = usePage();
const contact = computed(() => page.props.site.contact);

const mapUrl = computed(
    () =>
        `https://www.google.com/maps?q=${encodeURIComponent(contact.value.address ?? 'Uyo, Akwa Ibom State, Nigeria')}&output=embed`,
);

const subjects = [
    'General enquiry',
    'Programmes',
    'Research collaboration',
    'Media',
    'Something else',
];
</script>

<template>
    <Head title="Contact Us" />

    <div>
        <PageHeader
            eyebrow="Contact Us"
            title="We would love to hear from you"
            :breadcrumbs="[{ title: 'Contact Us', href: contactRoute.url() }]"
        >
            <p>
                Whether you have a question about our work, an idea for
                collaboration or would like to support a programme, get in touch
                with the CHICD team.
            </p>
        </PageHeader>

        <section class="py-14 sm:py-24">
            <!--
                On phones the order is details, form, map so the form is not
                pushed below the map; on large screens details and map share
                the left column beside the form.
            -->
            <div
                class="site-container grid gap-12 lg:grid-cols-12 lg:grid-rows-[auto_1fr] lg:gap-x-16 lg:gap-y-10"
            >
                <div class="space-y-8 lg:col-span-5">
                    <ul
                        class="divide-y divide-stone-200 border-y border-stone-200"
                    >
                        <li v-if="contact.address" class="flex gap-4 py-5">
                            <MapPin class="mt-1 size-5 shrink-0 text-sun-600" />
                            <div>
                                <p
                                    class="font-display font-bold tracking-[0.1em] text-brand-800 uppercase"
                                >
                                    Office
                                </p>
                                <p class="mt-1 text-ink">
                                    {{ contact.address }}
                                </p>
                            </div>
                        </li>
                        <li v-if="contact.email" class="flex gap-4 py-5">
                            <Mail class="mt-1 size-5 shrink-0 text-sun-600" />
                            <div>
                                <p
                                    class="font-display font-bold tracking-[0.1em] text-brand-800 uppercase"
                                >
                                    Email
                                </p>
                                <a
                                    :href="`mailto:${contact.email}`"
                                    class="mt-1 block font-semibold text-brand-700 hover:underline"
                                    >{{ contact.email }}</a
                                >
                            </div>
                        </li>
                        <li v-if="contact.phone" class="flex gap-4 py-5">
                            <Phone class="mt-1 size-5 shrink-0 text-sun-600" />
                            <div>
                                <p
                                    class="font-display font-bold tracking-[0.1em] text-brand-800 uppercase"
                                >
                                    Phone
                                </p>
                                <a
                                    :href="`tel:${contact.phone}`"
                                    class="mt-1 block font-semibold text-brand-700 hover:underline"
                                    >{{ contact.phone }}</a
                                >
                            </div>
                        </li>
                    </ul>

                    <SocialLinks />
                </div>

                <FormPanel
                    class="lg:col-span-7 lg:col-start-6 lg:row-span-2 lg:row-start-1"
                >
                    <h2 class="font-heading text-[1.8rem] font-bold text-ink">
                        Send us a message
                    </h2>
                    <p class="mt-2 mb-8 text-stone-700">
                        We aim to respond to all enquiries as soon as possible.
                    </p>
                    <EnquiryForm
                        type="general"
                        show-organisation
                        :subject-options="subjects"
                    />
                </FormPanel>

                <div class="border-t-[5px] border-brand-600 lg:col-span-5">
                    <iframe
                        :src="mapUrl"
                        title="Map showing Uyo, Akwa Ibom State, Nigeria"
                        class="aspect-[4/3] w-full bg-cream"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    />
                </div>
            </div>
        </section>
    </div>
</template>
