<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Menu } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import LanguagePicker from '@/components/site/LanguagePicker.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SiteLogo from '@/components/site/SiteLogo.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { SiteNavGroup } from '@/lib/siteContent';
import { donateLink, mainNavigationWithProgrammes } from '@/lib/siteContent';
import { home, login } from '@/routes';

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const page = usePage();

const mainNavigation = computed(() =>
    mainNavigationWithProgrammes(page.props.site.programmes),
);

const openGroup = ref<string | null>(null);

/** Index of the link whose photo is previewed, per dropdown. */
const previewIndexes = ref<Record<string, number>>({});

function previewIndex(group: string): number {
    return previewIndexes.value[group] ?? 0;
}

function setPreview(group: string, index: number): void {
    previewIndexes.value = { ...previewIndexes.value, [group]: index };
}

const isMobileMenuOpen = ref(false);

let closeTimer: ReturnType<typeof setTimeout> | undefined;

function openDropdown(title: string): void {
    clearTimeout(closeTimer);

    if (openGroup.value !== title) {
        setPreview(title, 0);
    }

    openGroup.value = title;
}

function scheduleClose(): void {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(() => (openGroup.value = null), 120);
}

function toggleDropdown(title: string): void {
    if (openGroup.value === title) {
        openGroup.value = null;

        return;
    }

    openDropdown(title);
}

function isActive(item: SiteNavGroup): boolean {
    if (item.href === home.url()) {
        return isCurrentUrl(item.href);
    }

    const sectionPrefix = item.href.split('/').slice(0, 2).join('/');

    return (
        isCurrentOrParentUrl(sectionPrefix) ||
        (item.children ?? []).some((child) => isCurrentUrl(child.href))
    );
}

function closeOnEscape(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        openGroup.value = null;
    }
}

let removeNavigateListener: (() => void) | undefined;

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    removeNavigateListener = router.on('navigate', () => {
        openGroup.value = null;
        isMobileMenuOpen.value = false;
    });
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', closeOnEscape);
    removeNavigateListener?.();
    clearTimeout(closeTimer);
});
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-stone-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/90"
    >
        <div
            class="site-container relative flex h-[4.5rem] items-center justify-between gap-4 lg:h-[5.25rem]"
        >
            <Link :href="home()" class="shrink-0" aria-label="CHICD home">
                <SiteLogo />
            </Link>

            <nav aria-label="Main" class="hidden h-full lg:block">
                <ul class="flex h-full items-stretch">
                    <li
                        v-for="item in mainNavigation"
                        :key="item.title"
                        class="flex"
                        @mouseenter="item.children && openDropdown(item.title)"
                        @mouseleave="item.children && scheduleClose()"
                    >
                        <Link
                            v-if="!item.children"
                            :href="item.href"
                            class="relative flex items-center px-3 font-body text-[0.98rem] font-semibold transition-colors after:absolute after:inset-x-3 after:bottom-0 after:h-1 after:bg-sun-400 after:transition-transform xl:px-4 xl:after:inset-x-4"
                            :class="
                                isActive(item)
                                    ? 'text-brand-800 after:scale-x-100'
                                    : 'text-ink after:scale-x-0 hover:text-brand-700'
                            "
                            >{{ item.title }}</Link
                        >
                        <template v-else>
                            <button
                                type="button"
                                class="relative flex items-center gap-1 px-3 font-body text-[0.98rem] font-semibold transition-colors after:absolute after:inset-x-3 after:bottom-0 after:h-1 after:bg-sun-400 after:transition-transform xl:px-4 xl:after:inset-x-4"
                                :class="
                                    isActive(item) || openGroup === item.title
                                        ? 'text-brand-800 after:scale-x-100'
                                        : 'text-ink after:scale-x-0 hover:text-brand-700'
                                "
                                :aria-expanded="openGroup === item.title"
                                aria-haspopup="true"
                                @click="toggleDropdown(item.title)"
                            >
                                {{ item.title }}
                                <ChevronDown
                                    class="size-4 text-stone-400 transition-transform"
                                    :class="{
                                        'rotate-180': openGroup === item.title,
                                    }"
                                />
                            </button>
                            <Transition
                                enter-active-class="transition duration-150 ease-out"
                                enter-from-class="-translate-y-1 opacity-0"
                                leave-active-class="transition duration-100 ease-in"
                                leave-to-class="-translate-y-1 opacity-0"
                            >
                                <div
                                    v-show="openGroup === item.title"
                                    class="absolute top-full left-1/2 w-[min(52rem,calc(100vw-3rem))] -translate-x-1/2"
                                >
                                    <div
                                        class="grid grid-cols-[minmax(0,1fr)_19rem] border-t-4 border-sun-400 bg-white shadow-[0_24px_48px_-16px_rgb(10_36_25/0.28)] xl:grid-cols-[minmax(0,1fr)_21rem]"
                                    >
                                        <div class="px-4 py-6">
                                            <p
                                                class="site-label px-4 pb-3 text-[0.85rem]"
                                            >
                                                {{ item.title }}
                                            </p>
                                            <ul
                                                class="max-h-[calc(100vh-14rem)] overflow-y-auto"
                                            >
                                                <li
                                                    v-for="(
                                                        child, index
                                                    ) in item.children"
                                                    :key="child.title"
                                                >
                                                    <Link
                                                        :href="child.href"
                                                        class="flex items-center gap-4 border-l-4 px-4 py-3.5 transition-colors focus-visible:outline-none"
                                                        :class="
                                                            previewIndex(
                                                                item.title,
                                                            ) === index
                                                                ? 'border-brand-600 bg-cream'
                                                                : 'border-transparent'
                                                        "
                                                        :aria-current="
                                                            isCurrentUrl(
                                                                child.href,
                                                            )
                                                                ? 'page'
                                                                : undefined
                                                        "
                                                        @mouseenter="
                                                            setPreview(
                                                                item.title,
                                                                index,
                                                            )
                                                        "
                                                        @focus="
                                                            setPreview(
                                                                item.title,
                                                                index,
                                                            )
                                                        "
                                                    >
                                                        <span
                                                            class="min-w-0 flex-1"
                                                        >
                                                            <span
                                                                class="block font-heading text-[1.05rem] font-bold text-ink"
                                                                >{{
                                                                    child.title
                                                                }}</span
                                                            >
                                                            <span
                                                                v-if="
                                                                    child.description
                                                                "
                                                                class="mt-0.5 block text-[0.92rem] leading-snug text-stone-600"
                                                                >{{
                                                                    child.description
                                                                }}</span
                                                            >
                                                        </span>
                                                        <ChevronRight
                                                            class="size-5 shrink-0 text-sun-500 transition-all"
                                                            :class="
                                                                previewIndex(
                                                                    item.title,
                                                                ) === index
                                                                    ? 'translate-x-0 opacity-100'
                                                                    : '-translate-x-1 opacity-0'
                                                            "
                                                        />
                                                    </Link>
                                                </li>
                                            </ul>
                                        </div>
                                        <Link
                                            :href="
                                                item.children[
                                                    previewIndex(item.title)
                                                ]?.href ?? item.href
                                            "
                                            class="relative isolate flex min-h-72 flex-col justify-end overflow-hidden bg-brand-900 p-6 text-white"
                                            tabindex="-1"
                                            aria-hidden="true"
                                        >
                                            <img
                                                v-for="(
                                                    child, index
                                                ) in item.children"
                                                :key="child.title"
                                                :src="child.image?.src"
                                                alt=""
                                                class="absolute inset-0 -z-20 size-full object-cover transition-opacity duration-300"
                                                :class="
                                                    previewIndex(item.title) ===
                                                    index
                                                        ? 'opacity-100'
                                                        : 'opacity-0'
                                                "
                                            />
                                            <span
                                                class="absolute inset-x-0 bottom-0 -z-10 h-2/3 bg-gradient-to-t from-brand-950/85 to-transparent"
                                            />
                                            <span
                                                class="font-display text-2xl leading-none font-bold uppercase"
                                                >{{
                                                    item.children[
                                                        previewIndex(item.title)
                                                    ]?.title
                                                }}</span
                                            >
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </template>
                    </li>
                </ul>
            </nav>

            <div class="flex items-center gap-2">
                <SiteButton
                    :href="donateLink.href"
                    variant="donate"
                    size="sm"
                    class="px-5 text-[1.05rem] sm:h-11 sm:px-6"
                >
                    {{ donateLink.title }}
                </SiteButton>

                <Sheet v-model:open="isMobileMenuOpen">
                    <SheetTrigger as-child>
                        <button
                            type="button"
                            class="flex size-11 items-center justify-center rounded-[3px] text-brand-900 hover:bg-cream lg:hidden"
                        >
                            <Menu class="size-6" />
                            <span class="sr-only">Open menu</span>
                        </button>
                    </SheetTrigger>
                    <SheetContent
                        side="right"
                        class="w-full gap-0 overflow-y-auto border-stone-200 bg-white text-ink sm:max-w-sm"
                    >
                        <div class="border-b-4 border-sun-400 px-6 py-5">
                            <SheetTitle class="sr-only">Site menu</SheetTitle>
                            <SheetDescription class="sr-only"
                                >Navigate the CHICD website</SheetDescription
                            >
                            <SiteLogo :show-tagline="false" />
                        </div>
                        <nav aria-label="Mobile" class="px-6 pb-8">
                            <ul class="divide-y divide-stone-200">
                                <li
                                    v-for="item in mainNavigation"
                                    :key="item.title"
                                >
                                    <Link
                                        v-if="!item.children"
                                        :href="item.href"
                                        class="block py-4 font-display text-xl font-bold uppercase"
                                        :class="
                                            isActive(item)
                                                ? 'text-brand-700'
                                                : 'text-ink'
                                        "
                                        >{{ item.title }}</Link
                                    >
                                    <details
                                        v-else
                                        class="group"
                                        :open="isActive(item)"
                                    >
                                        <summary
                                            class="flex cursor-pointer list-none items-center justify-between py-4 font-display text-xl font-bold text-ink uppercase [&::-webkit-details-marker]:hidden"
                                        >
                                            {{ item.title }}
                                            <ChevronDown
                                                class="size-5 text-stone-400 transition-transform group-open:rotate-180"
                                            />
                                        </summary>
                                        <ul class="mb-4 space-y-1">
                                            <li
                                                v-for="child in item.children"
                                                :key="child.title"
                                            >
                                                <Link
                                                    :href="child.href"
                                                    class="block border-l-4 py-2 pl-4 text-[1rem]"
                                                    :class="
                                                        isCurrentUrl(child.href)
                                                            ? 'border-sun-400 font-semibold text-brand-800'
                                                            : 'border-stone-200 text-stone-700'
                                                    "
                                                    >{{ child.title }}</Link
                                                >
                                            </li>
                                        </ul>
                                    </details>
                                </li>
                            </ul>
                            <SiteButton
                                :href="donateLink.href"
                                variant="donate"
                                size="lg"
                                class="mt-6 w-full"
                            >
                                Donate to CHICD
                            </SiteButton>
                            <LanguagePicker class="mt-6" />
                            <Link
                                :href="login()"
                                class="mt-6 block text-sm text-stone-500 hover:text-brand-700"
                                >Staff portal</Link
                            >
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </header>
</template>
