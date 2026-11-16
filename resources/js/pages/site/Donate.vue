<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CreditCard, GraduationCap, House, Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import EnquiryForm from '@/components/site/EnquiryForm.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import type { DonationFund } from '@/lib/siteContent';
import { donationFunds, siteImages } from '@/lib/siteContent';
import { donate } from '@/routes';

const page = usePage();

const selectedFund = computed<DonationFund>(() => {
    const fund = new URLSearchParams(page.url.split('?')[1] ?? '').get('fund');

    return donationFunds.some((option) => option.value === fund)
        ? (fund as DonationFund)
        : 'general';
});

const selectedFundLabel = computed(
    () =>
        donationFunds.find((option) => option.value === selectedFund.value)
            ?.label ?? donationFunds[0].label,
);

const impactAreas = [
    {
        icon: GraduationCap,
        title: 'Education through SURL',
        description:
            'Uniforms, school essentials, reading and digital learning support so children can participate in school with dignity.',
        accent: 'border-sun-400',
    },
    {
        icon: House,
        title: 'Safe Housing',
        description:
            'Temporary accommodation and essential residential facilities for vulnerable and marginalised adults.',
        accent: 'border-brand-600',
    },
    {
        icon: Sparkles,
        title: 'Community initiatives',
        description:
            'Health innovation, research and community development initiatives that address barriers to healthcare, education, safety and well-being.',
        accent: 'border-terracotta-600',
    },
];
</script>

<template>
    <Head title="Donate" />

    <div>
        <PageHeader
            :image="siteImages.health"
            eyebrow="Donate"
            title="Your Support Can Help Change Lives"
            :breadcrumbs="[{ title: 'Donate', href: donate.url() }]"
        >
            <p>
                Behind many health and development challenges are individuals
                and families who need practical support, access to opportunities
                and systems that respond to their circumstances.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="How your contribution helps"
                    title="Support that reaches people"
                >
                    <p>
                        By supporting CHICD, you can contribute to initiatives
                        that address barriers to healthcare, education, safety
                        and community well-being.
                    </p>
                </SectionIntro>
                <ul class="mt-12 grid gap-6 md:grid-cols-3">
                    <li
                        v-for="area in impactAreas"
                        :key="area.title"
                        class="flex gap-4 border-t-[5px] bg-cream/60 p-5 sm:block sm:p-7"
                        :class="area.accent"
                    >
                        <component
                            :is="area.icon"
                            class="size-7 shrink-0 text-brand-700 sm:size-8"
                            stroke-width="1.6"
                        />
                        <div>
                            <h3
                                class="font-heading text-xl font-bold text-ink sm:mt-5"
                            >
                                {{ area.title }}
                            </h3>
                            <p class="mt-2 leading-relaxed text-stone-700">
                                {{ area.description }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <section id="give" class="relative scroll-mt-28 bg-cream lg:py-24">
            <img
                :src="siteImages.surl.src"
                alt=""
                loading="lazy"
                class="absolute inset-0 hidden size-full object-cover lg:block"
            />
            <div
                class="site-container relative py-14 lg:flex lg:justify-end lg:py-0"
            >
                <div
                    class="-mx-5 border-t-[6px] border-sun-400 bg-white px-5 py-8 shadow-2xl sm:mx-0 sm:p-10 lg:max-w-[40rem]"
                >
                    <h2
                        class="font-display text-[2.4rem] leading-none font-extrabold text-brand-900 uppercase"
                    >
                        Make a donation
                    </h2>
                    <p class="mt-4 leading-relaxed text-stone-700">
                        You can give to CHICD where it is needed most, or
                        support a specific programme, as a one-time or recurring
                        gift.
                    </p>

                    <div
                        class="mt-6 flex gap-4 border-l-4 border-brand-600 bg-brand-50 p-5"
                    >
                        <CreditCard
                            class="mt-0.5 size-6 shrink-0 text-brand-700"
                        />
                        <div>
                            <h3 class="font-bold text-ink">
                                Secure online giving is coming soon
                            </h3>
                            <p class="mt-1 leading-relaxed text-stone-700">
                                We are setting up secure online card and bank
                                payments. Until then, send us your details and
                                we will contact you with the safest way to
                                complete your gift and a receipt.
                            </p>
                        </div>
                    </div>

                    <p class="mt-6 border-y border-stone-200 py-4">
                        <span
                            class="block font-display text-[0.95rem] font-bold tracking-[0.12em] text-sun-700 uppercase"
                            >You are giving to</span
                        >
                        <span
                            class="mt-1 block font-heading text-xl font-bold text-ink"
                            >{{ selectedFundLabel }}</span
                        >
                    </p>

                    <EnquiryForm
                        :key="selectedFund"
                        type="donation"
                        show-organisation
                        subject-label="I would like to support"
                        :subject-options="
                            donationFunds.map((option) => option.label)
                        "
                        :default-subject="selectedFundLabel"
                        message-label="Your gift"
                        message-placeholder="For example: a one-time gift of ₦50,000, or a monthly gift of ₦10,000."
                        submit-label="Send my pledge"
                        class="mt-6"
                    />
                </div>
            </div>
        </section>
    </div>
</template>
