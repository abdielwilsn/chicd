<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Download, HandCoins } from '@lucide/vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteButtonVariants } from '@/components/site/siteButtonVariants';
import PortalEmptyState from '@/components/staff/PortalEmptyState.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { exportMethod, index } from '@/routes/admin/donations';
import type { AdminDonation } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Donations', href: index() }],
    },
});

defineProps<{
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string | null;
    totals: { count: number; amount: string; fees: string; net: string };
    funds: { fund: string; count: number; amount: string }[];
    donations: AdminDonation[];
    incomplete: AdminDonation[];
}>();
</script>

<template>
    <Head title="Donations" />

    <div>
        <PortalPageHeader eyebrow="Admin" title="Donations">
            <p>Gifts paid through the website in {{ monthLabel }}.</p>

            <template #actions>
                <nav
                    aria-label="Choose a month"
                    class="flex items-center gap-2"
                >
                    <SiteButton
                        :href="index.url({ query: { month: previousMonth } })"
                        variant="ghostLight"
                        size="sm"
                        class="px-3"
                    >
                        <ChevronLeft />
                        <span class="sr-only">Previous month</span>
                    </SiteButton>
                    <span
                        class="min-w-36 text-center font-display text-xl font-bold tracking-[0.04em] uppercase"
                        >{{ monthLabel }}</span
                    >
                    <SiteButton
                        :href="
                            nextMonth
                                ? index.url({ query: { month: nextMonth } })
                                : undefined
                        "
                        :disabled="!nextMonth"
                        variant="ghostLight"
                        size="sm"
                        class="px-3"
                    >
                        <ChevronRight />
                        <span class="sr-only">Next month</span>
                    </SiteButton>
                </nav>
            </template>
        </PortalPageHeader>

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <section class="space-y-4">
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <dl class="grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-4">
                        <div>
                            <dt
                                class="font-display text-[0.85rem] font-bold tracking-[0.12em] text-muted-foreground uppercase"
                            >
                                Donations
                            </dt>
                            <dd
                                class="font-heading text-2xl font-bold tabular-nums"
                            >
                                {{ totals.count }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="font-display text-[0.85rem] font-bold tracking-[0.12em] text-muted-foreground uppercase"
                            >
                                Received
                            </dt>
                            <dd
                                class="font-heading text-2xl font-bold tabular-nums"
                            >
                                {{ totals.amount }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="font-display text-[0.85rem] font-bold tracking-[0.12em] text-muted-foreground uppercase"
                            >
                                Paystack fees
                            </dt>
                            <dd
                                class="font-heading text-2xl font-bold tabular-nums"
                            >
                                {{ totals.fees }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="font-display text-[0.85rem] font-bold tracking-[0.12em] text-muted-foreground uppercase"
                            >
                                Net
                            </dt>
                            <dd
                                class="font-heading text-2xl font-bold tabular-nums"
                            >
                                {{ totals.net }}
                            </dd>
                        </div>
                    </dl>

                    <!-- A plain link so the browser downloads the file. -->
                    <a
                        :href="exportMethod.url(month)"
                        :class="
                            siteButtonVariants({
                                variant: 'outline',
                                size: 'sm',
                            })
                        "
                    >
                        <Download />
                        Export {{ monthLabel }} (CSV)
                    </a>
                </div>

                <ul
                    v-if="funds.length"
                    class="flex flex-wrap gap-2 text-[0.95rem]"
                    aria-label="Totals by fund"
                >
                    <li
                        v-for="fund in funds"
                        :key="fund.fund"
                        class="rounded-[3px] bg-secondary px-3 py-1.5"
                    >
                        <span class="font-semibold">{{ fund.fund }}</span>
                        · {{ fund.amount }} from {{ fund.count }}
                        {{ fund.count === 1 ? 'gift' : 'gifts' }}
                    </li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="font-heading text-2xl font-bold">Paid</h2>
                <div
                    v-if="donations.length"
                    class="overflow-x-auto border-y border-border bg-card"
                >
                    <table class="w-full min-w-[56rem] text-left">
                        <thead
                            class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Paid</th>
                                <th class="px-4 py-3">Donor</th>
                                <th class="px-4 py-3">Supporting</th>
                                <th class="px-4 py-3">Frequency</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="donation in donations"
                                :key="donation.id"
                            >
                                <td
                                    class="px-4 py-3 whitespace-nowrap tabular-nums"
                                >
                                    {{ donation.paid_on }}
                                    <span
                                        v-if="donation.channel"
                                        class="block text-[0.85rem] text-muted-foreground"
                                        >{{ donation.channel }}</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-heading font-bold">{{
                                        donation.donor_name
                                    }}</span>
                                    <span
                                        class="block text-[0.9rem] text-muted-foreground"
                                        >{{ donation.donor_email
                                        }}<template v-if="donation.donor_phone">
                                            ·
                                            {{ donation.donor_phone }}</template
                                        ></span
                                    >
                                </td>
                                <td class="px-4 py-3">{{ donation.fund }}</td>
                                <td class="px-4 py-3">
                                    {{ donation.frequency }}
                                    <span
                                        v-if="donation.is_renewal"
                                        class="block text-[0.85rem] text-muted-foreground"
                                        >Repeat payment</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ donation.amount }}
                                    <span
                                        v-if="donation.fees"
                                        class="block text-[0.85rem] text-muted-foreground"
                                        >{{ donation.fees }} fees</span
                                    >
                                </td>
                                <td
                                    class="px-4 py-3 font-mono text-[0.85rem] text-muted-foreground"
                                >
                                    {{ donation.reference }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <PortalEmptyState
                    v-else
                    :icon="HandCoins"
                    title="No donations this month"
                >
                    Gifts appear here once Paystack confirms the payment.
                </PortalEmptyState>
            </section>

            <section v-if="incomplete.length" class="space-y-3">
                <div>
                    <h2 class="font-heading text-2xl font-bold">
                        Started but not paid
                    </h2>
                    <p class="text-muted-foreground">
                        Donors who went to Paystack in {{ monthLabel }} but did
                        not finish paying, or whose payment failed. Useful when
                        a donor says they did not get a receipt.
                    </p>
                </div>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <li
                        v-for="donation in incomplete"
                        :key="donation.id"
                        class="flex flex-wrap items-center gap-x-4 gap-y-1 px-4 py-3"
                    >
                        <StatusBadge
                            :status="donation.status"
                            :label="donation.status_label"
                        />
                        <span class="font-heading font-bold">{{
                            donation.donor_name
                        }}</span>
                        <span class="text-muted-foreground">{{
                            donation.donor_email
                        }}</span>
                        <span>{{ donation.amount }} · {{ donation.fund }}</span>
                        <span
                            class="ml-auto text-[0.9rem] text-muted-foreground tabular-nums"
                            >{{ donation.started_on }}</span
                        >
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
