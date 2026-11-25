<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CreditCard,
    GraduationCap,
    House,
    LockKeyhole,
    Repeat,
    Sparkles,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import { donate } from '@/routes';
import { callback, store } from '@/routes/donations';
import { loadPaystack, openPaystackCheckout } from '@/lib/paystack';
import type { PaystackPayment } from '@/lib/paystack';
import type { DonationFrequencyValue, Option } from '@/types';

const props = defineProps<{
    funds: Option[];
    frequencies: Option[];
    selectedFund: string;
    amounts: { suggested: number[]; minimum: number; maximum: number };
}>();

const form = useForm({
    fund: props.selectedFund,
    frequency: 'one_time' as DonationFrequencyValue,
    amount: (props.amounts.suggested[1] ?? props.amounts.suggested[0] ?? '') as
        | number
        | '',
    name: '',
    email: '',
    phone: '',
    website: '',
});

const naira = new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    maximumFractionDigits: 0,
});

const formatNaira = (amount: number): string => naira.format(amount);

/** Errors that do not belong to a field, such as Paystack being unreachable. */
const formError = computed(
    () =>
        (form.errors as Partial<Record<string, string>>).payment ??
        form.errors.website,
);

const isRecurring = computed(() => form.frequency !== 'one_time');

/** Whether the Paystack popup is open for this gift. */
const paying = ref(false);

/** Whether the donor closed the popup without paying. */
const cancelled = ref(false);

// Fetch Paystack's script while the donor fills in the form, so the popup
// opens straight away. If it cannot load, the donor is sent to Paystack's
// checkout page when they give.
onMounted(() => {
    loadPaystack().catch(() => undefined);
});

const submitLabel = computed(() => {
    if (paying.value) {
        return 'Complete your gift in the Paystack window';
    }

    if (form.processing) {
        return 'Connecting to Paystack…';
    }

    if (typeof form.amount !== 'number' || form.amount <= 0) {
        return 'Continue to secure payment';
    }

    const suffix =
        form.frequency === 'monthly'
            ? ' a month'
            : form.frequency === 'annually'
              ? ' a year'
              : '';

    return `Give ${formatNaira(form.amount)}${suffix}`;
});

const pay = (payment: PaystackPayment): void => {
    paying.value = true;

    void openPaystackCheckout(payment, {
        onSuccess: () => {
            router.visit(
                callback.url({ query: { reference: payment.reference } }),
            );
        },
        onCancel: () => {
            paying.value = false;
            cancelled.value = true;
        },
    });
};

const submit = (): void => {
    cancelled.value = false;

    form.post(store.url(), {
        preserveScroll: true,
        preserveState: true,
        onFlash: (flash) => {
            const payment = flash.paystack as PaystackPayment | undefined;

            if (payment) {
                pay(payment);
            }
        },
    });
};

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

                    <form class="mt-6 space-y-6" @submit.prevent="submit">
                        <div
                            class="absolute -left-[10000px] h-px w-px overflow-hidden"
                            aria-hidden="true"
                        >
                            <label for="donation-website"
                                >Leave this field empty</label
                            >
                            <input
                                id="donation-website"
                                v-model="form.website"
                                type="text"
                                tabindex="-1"
                                autocomplete="off"
                            />
                        </div>

                        <div>
                            <label for="donation-fund" class="site-label-text"
                                >I would like to support</label
                            >
                            <select
                                id="donation-fund"
                                v-model="form.fund"
                                class="site-input"
                                :aria-invalid="!!form.errors.fund"
                            >
                                <option
                                    v-for="fund in funds"
                                    :key="fund.value"
                                    :value="fund.value"
                                >
                                    {{ fund.label }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.fund"
                                class="mt-1.5"
                            />
                        </div>

                        <fieldset>
                            <legend class="site-label-text">How often</legend>
                            <div class="grid grid-cols-3 gap-2">
                                <label
                                    v-for="frequency in frequencies"
                                    :key="frequency.value"
                                    class="flex min-h-12 cursor-pointer items-center justify-center rounded-[3px] border-2 px-3 py-2 text-center font-semibold transition-colors has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-600/40"
                                    :class="
                                        form.frequency === frequency.value
                                            ? 'border-brand-700 bg-brand-700 text-white'
                                            : 'border-stone-300 text-ink hover:border-brand-600'
                                    "
                                >
                                    <input
                                        v-model="form.frequency"
                                        type="radio"
                                        name="frequency"
                                        :value="frequency.value"
                                        class="sr-only"
                                    />
                                    {{ frequency.label }}
                                </label>
                            </div>
                            <p
                                v-if="isRecurring"
                                class="mt-2 flex gap-2 text-[0.95rem] text-stone-600"
                            >
                                <Repeat
                                    class="mt-0.5 size-4 shrink-0 text-brand-700"
                                />
                                Paystack will charge your card on the same
                                schedule until you ask us to stop. You will get
                                a receipt each time.
                            </p>
                            <InputError
                                :message="form.errors.frequency"
                                class="mt-1.5"
                            />
                        </fieldset>

                        <fieldset>
                            <legend class="site-label-text">Amount</legend>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <button
                                    v-for="amount in amounts.suggested"
                                    :key="amount"
                                    type="button"
                                    class="min-h-12 rounded-[3px] border-2 px-3 py-2 font-semibold tabular-nums transition-colors focus-visible:ring-2 focus-visible:ring-brand-600/40 focus-visible:outline-none"
                                    :class="
                                        form.amount === amount
                                            ? 'border-brand-700 bg-brand-700 text-white'
                                            : 'border-stone-300 text-ink hover:border-brand-600'
                                    "
                                    :aria-pressed="form.amount === amount"
                                    @click="form.amount = amount"
                                >
                                    {{ formatNaira(amount) }}
                                </button>
                            </div>
                            <label
                                for="donation-amount"
                                class="mt-3 block text-[0.95rem] text-stone-600"
                                >Or enter another amount in naira</label
                            >
                            <div class="relative mt-1.5">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center font-semibold text-stone-500"
                                    aria-hidden="true"
                                    >₦</span
                                >
                                <input
                                    id="donation-amount"
                                    v-model.number="form.amount"
                                    type="number"
                                    inputmode="numeric"
                                    :min="amounts.minimum"
                                    :max="amounts.maximum"
                                    step="1"
                                    required
                                    class="site-input pl-8 tabular-nums"
                                    :aria-invalid="!!form.errors.amount"
                                />
                            </div>
                            <InputError
                                :message="form.errors.amount"
                                class="mt-1.5"
                            />
                        </fieldset>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label
                                    for="donation-name"
                                    class="site-label-text"
                                    >Full name</label
                                >
                                <input
                                    id="donation-name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    class="site-input"
                                    :aria-invalid="!!form.errors.name"
                                />
                                <InputError
                                    :message="form.errors.name"
                                    class="mt-1.5"
                                />
                            </div>
                            <div>
                                <label
                                    for="donation-email"
                                    class="site-label-text"
                                    >Email address</label
                                >
                                <input
                                    id="donation-email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    class="site-input"
                                    :aria-invalid="!!form.errors.email"
                                />
                                <p class="mt-1.5 text-sm text-stone-500">
                                    We send your receipt here.
                                </p>
                                <InputError
                                    :message="form.errors.email"
                                    class="mt-1.5"
                                />
                            </div>
                            <div>
                                <label
                                    for="donation-phone"
                                    class="site-label-text"
                                >
                                    Phone number
                                    <span class="font-normal text-stone-500"
                                        >(optional)</span
                                    >
                                </label>
                                <input
                                    id="donation-phone"
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                    class="site-input"
                                    :aria-invalid="!!form.errors.phone"
                                />
                                <InputError
                                    :message="form.errors.phone"
                                    class="mt-1.5"
                                />
                            </div>
                        </div>

                        <div
                            v-if="cancelled && !formError"
                            class="border-l-4 border-sun-400 bg-cream p-4 text-[0.98rem] text-ink"
                            role="status"
                        >
                            Your payment was cancelled and you have not been
                            charged. You can try again whenever you are ready.
                        </div>

                        <div
                            v-if="formError"
                            class="border-l-4 border-terracotta-600 bg-cream p-4 text-[0.98rem] text-ink"
                            role="alert"
                        >
                            <span :key="formError">{{ formError }}</span>
                        </div>

                        <SiteButton
                            type="submit"
                            variant="donate"
                            size="lg"
                            class="w-full"
                            :disabled="form.processing || paying"
                        >
                            <LockKeyhole />
                            <span :key="submitLabel">{{ submitLabel }}</span>
                        </SiteButton>

                        <p
                            class="flex gap-3 text-[0.95rem] leading-relaxed text-stone-600"
                        >
                            <CreditCard
                                class="mt-0.5 size-5 shrink-0 text-brand-700"
                            />
                            A secure Paystack window opens to take your payment.
                            CHICD never sees or stores your card details.
                        </p>
                    </form>
                </div>
            </div>
        </section>
    </div>
</template>
