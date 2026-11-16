<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BadgeCheck,
    ChartColumnIncreasing,
    HandHeart,
    Lightbulb,
    Network,
    Scale,
    Sparkles,
    Users,
} from '@lucide/vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import about from '@/routes/about';

const values = [
    {
        icon: Scale,
        title: 'Integrity',
        description:
            'We uphold honesty, ethical conduct and accountability in our decisions, relationships and use of resources.',
    },
    {
        icon: Lightbulb,
        title: 'Innovation',
        description:
            'We remain open to new ideas, technologies and practical approaches that can improve how health and development challenges are addressed.',
    },
    {
        icon: BadgeCheck,
        title: 'Evidence',
        description:
            'We value research, reliable information and critical assessment. Our decisions should be informed by what is known about a problem and strengthened by what we learn through implementation.',
    },
    {
        icon: Users,
        title: 'Inclusion',
        description:
            'We recognise that opportunities and essential services are not equally accessible to everyone. We seek to understand and address barriers that prevent individuals and communities from participating fully in development.',
    },
    {
        icon: Sparkles,
        title: 'Empowerment',
        description:
            "We believe meaningful support should strengthen people's knowledge, abilities and opportunities, enabling them to make decisions and improve their circumstances.",
    },
    {
        icon: HandHeart,
        title: 'Respect',
        description:
            'We recognise the dignity, experiences and perspectives of every individual and community with whom we work.',
    },
    {
        icon: Network,
        title: 'Collaboration',
        description:
            'We value the contributions of communities, professionals, institutions and development partners. Complex challenges often require different forms of expertise and shared responsibility.',
    },
    {
        icon: ChartColumnIncreasing,
        title: 'Impact',
        description:
            "We are concerned with the difference our work makes in people's lives. We seek meaningful improvements, measurable where appropriate and capable of lasting beyond individual activities.",
    },
];
</script>

<template>
    <Head title="Vision, Mission & Values" />

    <div>
        <PageHeader
            eyebrow="About CHICD"
            title="Vision, Mission & Values"
            :breadcrumbs="[
                { title: 'About Us', href: about.index.url() },
                { title: 'Vision, Mission & Values', href: about.values.url() },
            ]"
        />

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-6 lg:grid-cols-2">
                <article class="bg-brand-800 p-8 text-white sm:p-12">
                    <h2
                        class="font-display text-2xl font-extrabold tracking-[0.06em] text-sun-300 uppercase"
                    >
                        Our Vision
                    </h2>
                    <p
                        class="mt-6 font-heading text-[1.3rem] leading-snug font-semibold sm:text-[1.6rem] lg:text-[1.9rem]"
                    >
                        To be a leading organisation that advances innovative
                        solutions and empowers communities to achieve healthier
                        lives and sustainable development.
                    </p>
                </article>
                <article class="bg-sun-400 p-8 text-brand-950 sm:p-12">
                    <h2
                        class="font-display text-2xl font-extrabold tracking-[0.06em] text-brand-900 uppercase"
                    >
                        Our Mission
                    </h2>
                    <p
                        class="mt-6 font-heading text-[1.3rem] leading-snug font-semibold sm:text-[1.6rem] lg:text-[1.9rem]"
                    >
                        To improve lives through health innovation, research,
                        technology and inclusive community development
                        initiatives that expand knowledge, strengthen capacity
                        and create sustainable opportunities for individuals and
                        communities.
                    </p>
                </article>
            </div>
        </section>

        <section class="bg-cream py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="Our Values">
                    <p>
                        Our values guide how we engage with communities, manage
                        resources, build partnerships and assess the work we
                        undertake.
                    </p>
                </SectionIntro>
                <ul
                    class="mt-14 grid gap-x-10 gap-y-12 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <li
                        v-for="value in values"
                        :key="value.title"
                        class="border-t-2 border-brand-700 pt-6"
                    >
                        <component
                            :is="value.icon"
                            class="size-7 text-sun-600"
                            stroke-width="1.75"
                        />
                        <h3
                            class="mt-4 font-display text-[1.6rem] leading-none font-extrabold text-brand-900 uppercase"
                        >
                            {{ value.title }}
                        </h3>
                        <p class="mt-3 leading-relaxed text-stone-700">
                            {{ value.description }}
                        </p>
                    </li>
                </ul>
            </div>
        </section>
    </div>
</template>
