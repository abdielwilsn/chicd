<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, TriangleAlert } from '@lucide/vue';
import { reactive, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import StatusBadge from '@/components/staff/StatusBadge.vue';
import { approve, index, pay, reopen, update } from '@/routes/admin/payroll';
import { show as showTimesheet } from '@/routes/admin/timesheets';
import type { PayrollRow } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Payroll', href: index() }],
    },
});

const props = defineProps<{
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string | null;
    staff: PayrollRow[];
    totals: { pending: number; approved: number; paid: number };
}>();

type Draft = { adjustment: string; note: string };

/** The adjustment being typed for each staff member, keyed by id. */
const drafts = reactive<Record<number, Draft>>({});
const rowErrors = reactive<Record<number, string | undefined>>({});

watch(
    () => props.staff,
    (rows) => {
        for (const row of rows) {
            drafts[row.id] = {
                adjustment:
                    row.adjustment === null ? '' : String(row.adjustment),
                note: row.adjustmentNote ?? '',
            };
        }
    },
    { immediate: true },
);

const options = (row: PayrollRow) => ({
    preserveScroll: true,
    onError: (errors: Record<string, string>) => {
        rowErrors[row.id] =
            errors.payroll ?? errors.note ?? errors.adjustment ?? undefined;
    },
    onSuccess: () => {
        rowErrors[row.id] = undefined;
    },
});

const route = (row: PayrollRow) => ({ user: row.id, month: props.month });

const saveAdjustment = (row: PayrollRow): void => {
    router.patch(
        update.url(route(row)),
        {
            adjustment: drafts[row.id].adjustment,
            note: drafts[row.id].note,
        },
        options(row),
    );
};

const approvePay = (row: PayrollRow): void => {
    router.post(approve.url(route(row)), {}, options(row));
};

const markPaid = (row: PayrollRow): void => {
    if (
        !window.confirm(
            `Mark ${row.approvedPay} as paid to ${row.name}? This cannot be undone.`,
        )
    ) {
        return;
    }

    router.post(pay.url(route(row)), {}, options(row));
};

const moveBackToPending = (row: PayrollRow): void => {
    router.delete(reopen.url(route(row)), options(row));
};
</script>

<template>
    <Head title="Payroll" />

    <div>
        <PortalPageHeader eyebrow="Admin only" title="Payroll">
            <p>
                Pay for {{ monthLabel }}: {{ totals.pending }} pending,
                {{ totals.approved }} approved, {{ totals.paid }} paid.
            </p>

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

        <div class="portal-container space-y-4 py-8 sm:py-10">
            <p class="text-muted-foreground">
                Approved pay is the monthly salary plus the attendance
                adjustment (negative for a deduction). Pay can be approved once
                the month's timesheet is approved; the salary is then fixed for
                that month.
            </p>

            <ul class="divide-y divide-border border-y border-border bg-card">
                <li
                    v-for="row in staff"
                    :key="row.id"
                    class="grid gap-4 px-4 py-5 lg:grid-cols-[1fr_1.3fr_auto]"
                >
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-heading text-lg font-bold">{{
                                row.name
                            }}</span>
                            <StatusBadge
                                :status="row.status"
                                :label="row.statusLabel"
                            />
                        </div>
                        <Link
                            :href="showTimesheet({ user: row.id, month })"
                            class="block text-[0.95rem] text-brand-700 underline-offset-4 hover:underline dark:text-brand-200"
                        >
                            Timesheet: {{ row.timesheetLabel }}
                        </Link>
                        <p class="text-[0.9rem] text-muted-foreground">
                            {{ row.attendance.lateDays }} late ·
                            {{ row.attendance.absentDays }} absent ·
                            {{ row.attendance.leaveDays }} on leave, of
                            {{ row.attendance.workingDays }} working days
                        </p>
                        <p
                            v-if="row.approvedOn"
                            class="text-[0.9rem] text-muted-foreground"
                        >
                            Approved {{ row.approvedOn }} by
                            {{ row.approvedBy ?? 'an admin' }}
                            <template v-if="row.paidOn">
                                · paid {{ row.paidOn }} by
                                {{ row.paidBy ?? 'an admin' }}</template
                            >
                        </p>
                    </div>

                    <div class="space-y-2">
                        <dl class="grid grid-cols-3 gap-3 text-[0.95rem]">
                            <div>
                                <dt class="text-muted-foreground">Salary</dt>
                                <dd class="font-semibold tabular-nums">
                                    {{ row.monthlySalary ?? 'Not set' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">
                                    Adjustment
                                </dt>
                                <dd class="font-semibold tabular-nums">
                                    {{ row.adjustmentLabel ?? '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">
                                    Approved pay
                                </dt>
                                <dd class="font-semibold tabular-nums">
                                    {{ row.approvedPay ?? '—' }}
                                </dd>
                            </div>
                        </dl>
                        <p
                            v-if="
                                row.adjustmentNote && row.status !== 'pending'
                            "
                            class="text-[0.9rem] text-muted-foreground"
                        >
                            {{ row.adjustmentNote }}
                        </p>
                        <form
                            v-if="row.status === 'pending'"
                            class="flex flex-wrap items-end gap-2"
                            @submit.prevent="saveAdjustment(row)"
                        >
                            <div class="w-32">
                                <label
                                    :for="`adjustment-${row.id}`"
                                    class="text-[0.85rem] text-muted-foreground"
                                    >Adjustment (₦)</label
                                >
                                <input
                                    :id="`adjustment-${row.id}`"
                                    v-model="drafts[row.id].adjustment"
                                    type="number"
                                    step="0.01"
                                    placeholder="0"
                                    class="portal-input py-2 tabular-nums"
                                />
                            </div>
                            <div class="min-w-40 flex-1">
                                <label
                                    :for="`adjustment-note-${row.id}`"
                                    class="text-[0.85rem] text-muted-foreground"
                                    >Reason</label
                                >
                                <input
                                    :id="`adjustment-note-${row.id}`"
                                    v-model="drafts[row.id].note"
                                    type="text"
                                    placeholder="e.g. 2 unexplained absences"
                                    class="portal-input py-2"
                                />
                            </div>
                            <SiteButton
                                type="submit"
                                size="sm"
                                variant="outline"
                            >
                                Save
                            </SiteButton>
                        </form>
                    </div>

                    <div class="flex flex-col items-start gap-2 lg:items-end">
                        <template v-if="row.status === 'pending'">
                            <SiteButton
                                type="button"
                                size="sm"
                                :disabled="row.approvalBlockers.length > 0"
                                @click="approvePay(row)"
                            >
                                Approve pay
                            </SiteButton>
                            <ul
                                v-if="row.approvalBlockers.length"
                                class="space-y-0.5 text-[0.85rem] text-muted-foreground lg:text-right"
                            >
                                <li
                                    v-for="blocker in row.approvalBlockers"
                                    :key="blocker"
                                    class="flex gap-1.5 lg:justify-end"
                                >
                                    <TriangleAlert
                                        class="mt-0.5 size-3.5 shrink-0 text-sun-700"
                                    />
                                    {{ blocker }}
                                </li>
                            </ul>
                        </template>
                        <template v-else-if="row.status === 'approved'">
                            <SiteButton
                                type="button"
                                size="sm"
                                variant="donate"
                                @click="markPaid(row)"
                            >
                                Mark as paid
                            </SiteButton>
                            <button
                                type="button"
                                class="text-[0.9rem] font-semibold text-brand-700 hover:underline dark:text-brand-200"
                                @click="moveBackToPending(row)"
                            >
                                Move back to Pending
                            </button>
                        </template>
                        <InputError :message="rowErrors[row.id]" />
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
