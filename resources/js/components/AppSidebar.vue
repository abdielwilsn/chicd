<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarDays, Clock, Globe } from '@lucide/vue';
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
import { index as myAttendance } from '@/routes/attendance';
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
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
