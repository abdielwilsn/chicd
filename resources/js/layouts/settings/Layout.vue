<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PortalPageHeader from '@/components/staff/PortalPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Security',
        href: editSecurity(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div>
        <PortalPageHeader eyebrow="Your account" title="Settings">
            <p>Manage your profile, password and sign-in security.</p>
        </PortalPageHeader>

        <div
            class="portal-container flex flex-col py-8 sm:py-10 lg:flex-row lg:gap-12"
        >
            <aside class="w-full max-w-xl lg:w-52">
                <nav class="flex flex-col gap-1" aria-label="Settings">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'h-10 w-full justify-start rounded-[3px] text-[0.98rem] font-semibold',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-secondary text-secondary-foreground shadow-[inset_4px_0_0_var(--color-sun-400)]'
                                : 'text-muted-foreground',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
