<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    CalendarDays,
    Clock,
    Globe,
    HandCoins,
    IdCard,
    Images,
    LayoutGrid,
    Library,
    MessageSquareWarning,
    Newspaper,
    Palmtree,
    Wallet,
    UserCog,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, home } from '@/routes';
import { index as staffAttendance } from '@/routes/admin/attendance';
import { index as correctionRequests } from '@/routes/admin/corrections';
import { index as donations } from '@/routes/admin/donations';
import { index as gallery } from '@/routes/admin/gallery';
import { index as leaveRequests } from '@/routes/admin/leave';
import { index as news } from '@/routes/admin/news';
import { index as opportunities } from '@/routes/admin/opportunities';
import { index as payroll } from '@/routes/admin/payroll';
import { index as programmes } from '@/routes/admin/programmes';
import { index as publications } from '@/routes/admin/publications';
import { index as staffAccounts } from '@/routes/admin/staff';
import { index as team } from '@/routes/admin/team';
import { index as myAttendance } from '@/routes/attendance';
import { index as myLeave } from '@/routes/leave';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Today',
        href: dashboard(),
        icon: Clock,
    },
    {
        title: 'My attendance',
        href: myAttendance(),
        icon: CalendarDays,
    },
    {
        title: 'My leave',
        href: myLeave(),
        icon: Palmtree,
    },
];

const page = usePage();

const isAdmin = computed(() => page.props.auth.user?.role === 'admin');

const adminNavItems: NavItem[] = [
    {
        title: 'Staff attendance',
        href: staffAttendance(),
        icon: Users,
    },
    {
        title: 'Correction requests',
        href: correctionRequests(),
        icon: MessageSquareWarning,
    },
    {
        title: 'Leave requests',
        href: leaveRequests(),
        icon: Palmtree,
    },
    {
        title: 'Payroll',
        href: payroll(),
        icon: Wallet,
    },
    {
        title: 'Staff accounts',
        href: staffAccounts(),
        icon: UserCog,
    },
    {
        title: 'Donations',
        href: donations(),
        icon: HandCoins,
    },
];

const websiteNavItems: NavItem[] = [
    {
        title: 'Programmes',
        href: programmes(),
        icon: LayoutGrid,
    },
    {
        title: 'Publications',
        href: publications(),
        icon: Library,
    },
    {
        title: 'Team page',
        href: team(),
        icon: IdCard,
    },
    {
        title: 'Opportunities',
        href: opportunities(),
        icon: BriefcaseBusiness,
    },
    {
        title: 'News & events',
        href: news(),
        icon: Newspaper,
    },
    {
        title: 'Media gallery',
        href: gallery(),
        icon: Images,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Public website',
        href: home(),
        icon: Globe,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <SidebarHeader
            class="border-b-4 border-sun-400 py-4 group-data-[collapsible=icon]:py-2"
        >
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="hover:bg-transparent active:bg-transparent"
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="pt-4">
            <NavMain :items="mainNavItems" />
            <NavMain v-if="isAdmin" :items="adminNavItems" label="Admin" />
            <NavMain v-if="isAdmin" :items="websiteNavItems" label="Website" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
