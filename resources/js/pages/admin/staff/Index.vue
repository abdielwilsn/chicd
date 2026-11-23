<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    KeyRound,
    ShieldCheck,
    UserMinus,
    UserPlus,
    UserRoundCheck,
} from '@lucide/vue';
import { reactive, watch } from 'vue';
import AdminField from '@/components/admin/AdminField.vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import PortalPanel from '@/components/staff/PortalPanel.vue';
import { Spinner } from '@/components/ui/spinner';
import {
    deactivate,
    index,
    passwordLink,
    reactivate,
    salary,
    store,
    update,
} from '@/routes/admin/staff';
import type { AdminStaffMember, Option } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Staff accounts', href: index() }],
    },
});

const props = defineProps<{
    staff: AdminStaffMember[];
    formerStaff: AdminStaffMember[];
    roles: Option[];
}>();

const form = useForm({
    name: '',
    email: '',
    role: 'staff',
});

const addStaffMember = (): void => {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const changeRole = (member: AdminStaffMember, role: string): void => {
    router.patch(update.url(member.id), { role }, { preserveScroll: true });
};

/** The salary being typed for each staff member, in naira, keyed by id. */
const salaries = reactive<Record<number, string>>({});

watch(
    () => props.staff,
    (staff) => {
        for (const member of staff) {
            salaries[member.id] =
                member.monthly_salary === null
                    ? ''
                    : String(member.monthly_salary);
        }
    },
    { immediate: true },
);

const saveSalary = (member: AdminStaffMember): void => {
    router.put(
        salary.url(member.id),
        { monthly_salary: salaries[member.id] },
        { preserveScroll: true },
    );
};

const sendPasswordLink = (member: AdminStaffMember): void => {
    router.post(passwordLink.url(member.id), {}, { preserveScroll: true });
};

const deactivateAccount = (member: AdminStaffMember): void => {
    if (
        !window.confirm(
            `Deactivate ${member.name}'s account? They will be signed out and will no longer be able to sign in. Their attendance records and timesheets are kept.`,
        )
    ) {
        return;
    }

    router.post(deactivate.url(member.id), {}, { preserveScroll: true });
};

const reactivateAccount = (member: AdminStaffMember): void => {
    router.delete(reactivate.url(member.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Staff accounts" />

    <div>
        <PortalPageHeader eyebrow="Admin" title="Staff accounts">
            <p>
                Everyone who can sign in to the staff portal. Admins can also
                review attendance, run payroll, manage donations and edit the
                website. Salaries are only ever shown to admins.
            </p>
        </PortalPageHeader>

        <div class="portal-container space-y-10 py-8 sm:py-10">
            <PortalPanel title="Add a staff member" :icon="UserPlus">
                <form
                    class="grid gap-5 sm:grid-cols-2"
                    @submit.prevent="addStaffMember"
                >
                    <AdminField
                        id="staff-name"
                        label="Full name"
                        :error="form.errors.name"
                    >
                        <input
                            id="staff-name"
                            v-model="form.name"
                            type="text"
                            required
                            autocomplete="off"
                            class="portal-input"
                            :aria-invalid="!!form.errors.name"
                        />
                    </AdminField>
                    <AdminField
                        id="staff-email"
                        label="Email address"
                        hint="They sign in with this address."
                        :error="form.errors.email"
                    >
                        <input
                            id="staff-email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="off"
                            class="portal-input"
                            :aria-invalid="!!form.errors.email"
                        />
                    </AdminField>
                    <AdminField
                        id="staff-role"
                        label="Permissions"
                        :error="form.errors.role"
                    >
                        <select
                            id="staff-role"
                            v-model="form.role"
                            class="portal-input"
                        >
                            <option
                                v-for="role in roles"
                                :key="role.value"
                                :value="role.value"
                            >
                                {{ role.label }}
                            </option>
                        </select>
                    </AdminField>
                    <div class="flex items-end">
                        <SiteButton type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            Add and email a password link
                        </SiteButton>
                    </div>
                    <p
                        class="text-[0.95rem] text-muted-foreground sm:col-span-2"
                    >
                        The staff member is emailed a link to choose their own
                        password. No one else ever sees it.
                    </p>
                </form>
            </PortalPanel>

            <section class="space-y-3">
                <h2 class="font-heading text-2xl font-bold">
                    {{ staff.length }}
                    {{ staff.length === 1 ? 'account' : 'accounts' }}
                </h2>
                <div class="overflow-x-auto border-y border-border bg-card">
                    <table class="w-full min-w-[60rem] text-left">
                        <thead
                            class="bg-secondary font-display text-[0.85rem] font-bold tracking-[0.1em] uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Staff member</th>
                                <th class="px-4 py-3">Permissions</th>
                                <th class="px-4 py-3">Monthly salary</th>
                                <th class="px-4 py-3">Sign-in</th>
                                <th class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="member in staff" :key="member.id">
                                <td class="px-4 py-3">
                                    <span class="font-heading font-bold">{{
                                        member.name
                                    }}</span>
                                    <span
                                        v-if="member.is_current_user"
                                        class="ml-2 text-[0.9rem] text-muted-foreground"
                                        >(you)</span
                                    >
                                    <span
                                        class="block text-[0.9rem] text-muted-foreground"
                                        >{{ member.email }} · added
                                        {{ member.added_on }}</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="member.is_current_user">{{
                                        member.role_label
                                    }}</span>
                                    <template v-else>
                                        <label
                                            :for="`role-${member.id}`"
                                            class="sr-only"
                                            >Permissions for
                                            {{ member.name }}</label
                                        >
                                        <select
                                            :id="`role-${member.id}`"
                                            :value="member.role"
                                            class="portal-input w-36 py-2"
                                            @change="
                                                changeRole(
                                                    member,
                                                    (
                                                        $event.target as HTMLSelectElement
                                                    ).value,
                                                )
                                            "
                                        >
                                            <option
                                                v-for="role in roles"
                                                :key="role.value"
                                                :value="role.value"
                                            >
                                                {{ role.label }}
                                            </option>
                                        </select>
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <form
                                        class="flex items-center gap-2"
                                        @submit.prevent="saveSalary(member)"
                                    >
                                        <label
                                            :for="`salary-${member.id}`"
                                            class="sr-only"
                                            >Monthly salary for
                                            {{ member.name }} in naira</label
                                        >
                                        <input
                                            :id="`salary-${member.id}`"
                                            v-model="salaries[member.id]"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            placeholder="Not set"
                                            class="portal-input w-36 py-2 tabular-nums"
                                        />
                                        <button
                                            type="submit"
                                            class="font-semibold text-brand-700 hover:underline dark:text-brand-200"
                                        >
                                            Save
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-[0.95rem]">
                                    <span
                                        v-if="member.has_two_factor"
                                        class="flex items-center gap-1.5"
                                    >
                                        <ShieldCheck
                                            class="size-4 text-brand-600"
                                        />
                                        Two-factor on
                                    </span>
                                    <span v-else class="text-muted-foreground"
                                        >Password only</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        v-if="!member.is_current_user"
                                        class="flex flex-col items-end gap-2"
                                    >
                                        <SiteButton
                                            type="button"
                                            variant="link"
                                            @click="sendPasswordLink(member)"
                                        >
                                            <KeyRound />
                                            Email a password link
                                        </SiteButton>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1.5 font-semibold text-destructive hover:underline"
                                            @click="deactivateAccount(member)"
                                        >
                                            <UserMinus class="size-4" />
                                            Deactivate (left CHICD)
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <InputError :message="$page.props.errors.role" />
                <InputError :message="$page.props.errors.account" />
            </section>

            <section v-if="formerStaff.length" class="space-y-3">
                <div>
                    <h2 class="font-heading text-2xl font-bold">
                        Former staff
                    </h2>
                    <p class="text-muted-foreground">
                        Deactivated accounts cannot sign in. Their attendance
                        records and timesheets are kept and still appear for the
                        months they worked.
                    </p>
                </div>
                <ul
                    class="divide-y divide-border border-y border-border bg-card"
                >
                    <li
                        v-for="member in formerStaff"
                        :key="member.id"
                        class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3"
                    >
                        <div class="min-w-0">
                            <span class="font-heading font-bold">{{
                                member.name
                            }}</span>
                            <span
                                class="block text-[0.9rem] text-muted-foreground"
                                >{{ member.email }} · deactivated
                                {{ member.deactivated_on
                                }}<template v-if="member.deactivated_by">
                                    by {{ member.deactivated_by }}</template
                                ></span
                            >
                        </div>
                        <SiteButton
                            type="button"
                            variant="link"
                            class="ml-auto"
                            @click="reactivateAccount(member)"
                        >
                            <UserRoundCheck />
                            Reactivate
                        </SiteButton>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
