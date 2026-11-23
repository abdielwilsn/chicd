<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CircleCheck, Clock, CircleAlert } from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import { donate, home } from '@/routes';
import { callback } from '@/routes/donations';
import type { DonationFrequencyValue, DonationStatusValue } from '@/types';

const props = defineProps<{
    donation: {
        reference: string;
        status: DonationStatusValue;
        firstName: string;
        amount: string;
        fund: string;
        frequency: DonationFrequencyValue;
        frequencyLabel: string;
    };
}>();

const outcome = computed(() => {
    switch (props.donation.status) {
        case 'successful':
            return {
                icon: CircleCheck,
                tone: 'border-brand-600 bg-brand-50',
                iconTone: 'text-brand-600',
                eyebrow: 'Thank you',
                title: `Thank you, ${props.donation.firstName}`,
                message:
                    'Your gift has been received. We have emailed you a receipt, and a member of the CHICD team has been told about your support.',
            };
        case 'pending':
            return {
                icon: Clock,
                tone: 'border-sun-400 bg-cream',
                iconTone: 'text-sun-700',
                eyebrow: 'Almost there',
                title: 'We are confirming your payment',
                message:
                    'Paystack has not confirmed your payment yet. This usually takes a moment. You will receive a receipt by email as soon as it is confirmed.',
            };
        default:
            return {
                icon: CircleAlert,
                tone: 'border-terracotta-600 bg-cream',
                iconTone: 'text-terracotta-600',
                eyebrow: 'Donation',
                title: 'Your payment was not completed',
                message:
                    'Your gift did not go through and you have not been charged. You can try again, or contact us if the problem continues.',
            };
    }
});
</script>

<template>
    <Head :title="outcome.title" />

    <div>
        <PageHeader
            :image="siteImages.health"
            :eyebrow="outcome.eyebrow"
            :title="outcome.title"
            :breadcrumbs="[{ title: 'Donate', href: donate.url() }]"
        />

        <section class="py-16 sm:py-20">
            <div class="site-container max-w-3xl">
                <div
                    class="flex gap-4 border-l-4 p-5 sm:p-6"
                    :class="outcome.tone"
                    role="status"
                >
                    <component
                        :is="outcome.icon"
                        class="mt-0.5 size-6 shrink-0"
                        :class="outcome.iconTone"
                    />
                    <p class="text-[1.0625rem] leading-relaxed text-ink">
                        {{ outcome.message }}
                    </p>
                </div>

                <dl
                    class="mt-8 divide-y divide-stone-200 border-y border-stone-200"
                >
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-600">Amount</dt>
                        <dd class="font-semibold text-ink tabular-nums">
                            {{ donation.amount }}
                            <span
                                v-if="donation.frequency !== 'one_time'"
                                class="font-normal text-stone-600"
                                >({{
                                    donation.frequencyLabel.toLowerCase()
                                }})</span
                            >
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-600">Supporting</dt>
                        <dd class="text-right font-semibold text-ink">
                            {{ donation.fund }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-600">Reference</dt>
                        <dd class="font-mono text-[0.95rem] text-ink">
                            {{ donation.reference }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-8 flex flex-wrap gap-3">
                    <SiteButton
                        v-if="donation.status === 'pending'"
                        :href="
                            callback.url({
                                query: { reference: donation.reference },
                            })
                        "
                    >
                        Check again
                    </SiteButton>
                    <SiteButton
                        v-else-if="donation.status !== 'successful'"
                        :href="donate.url()"
                        variant="donate"
                    >
                        Try again
                    </SiteButton>
                    <SiteButton :href="home.url()" variant="outline">
                        Back to the home page
                    </SiteButton>
                </div>
            </div>
        </section>
    </div>
</template>
