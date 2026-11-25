<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CallToAction from '@/components/site/CallToAction.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { siteImages } from '@/lib/siteContent';
import about from '@/routes/about';
import getInvolved from '@/routes/get-involved';

const journey = [
    {
        period: 'Around 2019',
        title: 'Community Public Health Experience',
        description:
            "The founder's involvement in community public health services, particularly among people living with HIV in rural communities across Akwa Ibom and Ekiti States, helped shape an understanding of the health and social challenges affecting underserved populations.",
    },
    {
        period: 'Following Years',
        title: 'Personal Charitable Support',
        description:
            'The commitment to community service grew into personally funded assistance with healthcare expenses and educational needs. Contributions from friends helped sustain these charitable efforts.',
    },
    {
        period: 'July 2026',
        title: 'Formal Registration of CHICD',
        description:
            'CHICD was formally registered to provide an institutional foundation for structured programmes, research, partnerships and sustainable community development initiatives.',
    },
    {
        period: '2026 and Beyond',
        title: 'Building for Sustainable Impact',
        description:
            'CHICD is developing the organisational capacity, programmes and partnerships needed to translate its founding commitment into coordinated, evidence-informed interventions.',
    },
];
</script>

<template>
    <Head title="Our Story" />

    <div>
        <PageHeader
            :image="siteImages.story"
            eyebrow="About CHICD"
            title="Our Story"
            :breadcrumbs="[
                { title: 'About Us', href: about.index.url() },
                { title: 'Our Story', href: about.story.url() },
            ]"
        >
            <p>
                CHICD's story began long before its formal registration. It grew
                from years of community public health service and a personal
                commitment to supporting people who struggled to access
                essential healthcare and educational opportunities.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="site-prose lg:col-span-7">
                    <p
                        class="first-letter:float-left first-letter:mt-1 first-letter:mr-3 first-letter:font-display first-letter:text-[4.6rem] first-letter:leading-[0.8] first-letter:font-extrabold first-letter:text-brand-700"
                    >
                        For approximately seven years before CHICD was
                        registered, its founder was involved in delivering
                        community-based public health services, particularly
                        among people living with HIV in rural communities across
                        Akwa Ibom and Ekiti States. Working in these communities
                        provided firsthand experience of the challenges
                        associated with accessing healthcare, obtaining reliable
                        health information and meeting basic needs.
                    </p>
                    <p>
                        These experiences strengthened a commitment to helping
                        people beyond formal professional responsibilities. Over
                        time, the founder began providing personal charitable
                        assistance to vulnerable individuals, particularly by
                        contributing to healthcare expenses and supporting their
                        educational needs. Friends who shared this commitment
                        also contributed resources, making it possible to extend
                        assistance to more people.
                    </p>
                    <p>
                        As these efforts continued, it became increasingly clear
                        that responding to individual needs, while important,
                        could not adequately address the wider conditions
                        contributing to poor health, limited opportunities and
                        social disadvantage. There was a need to build something
                        more structured that could bring together people,
                        knowledge, resources and partnerships to address
                        community challenges more sustainably.
                    </p>
                    <p>
                        This thinking led to the formal registration of the
                        Centre for Health Innovation and Community Development
                        in July, 2026.
                    </p>
                    <p>
                        The establishment of CHICD marked a transition from
                        personally supported charitable activities to an
                        organised nonprofit institution with a broader
                        commitment to research, innovation, health improvement
                        and community development.
                    </p>
                </div>
                <aside class="lg:col-span-5">
                    <figure class="lg:sticky lg:top-32">
                        <span
                            class="block font-heading text-[4.5rem] leading-[0.6] font-bold text-sun-400 sm:text-[6rem]"
                            aria-hidden="true"
                            >&ldquo;</span
                        >
                        <blockquote
                            class="mt-2 font-heading text-[1.3rem] leading-snug font-semibold text-brand-900 sm:text-[1.6rem]"
                        >
                            Meaningful development requires more than
                            identifying problems. It requires understanding the
                            people affected, working with them to develop
                            appropriate responses and strengthening their
                            ability to build better futures.
                        </blockquote>
                        
                    </figure>
                </aside>
            </div>
        </section>

        <section class="bg-cream py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="Our Journey" />
                <ol class="mt-14 grid gap-10 lg:grid-cols-4 lg:gap-8">
                    <li
                        v-for="(milestone, index) in journey"
                        :key="milestone.title"
                        class="relative border-l-4 pl-6 lg:border-t-4 lg:border-l-0 lg:pt-8 lg:pl-0"
                        :class="
                            index === 2 ? 'border-sun-400' : 'border-brand-700'
                        "
                    >
                        <p
                            class="font-display text-xl font-bold tracking-[0.06em] uppercase"
                            :class="
                                index === 2 ? 'text-sun-700' : 'text-brand-700'
                            "
                        >
                            {{ milestone.period }}
                        </p>
                        <h3
                            class="mt-2 font-heading text-xl leading-snug font-bold text-ink"
                        >
                            {{ milestone.title }}
                        </h3>
                        <p class="mt-3 leading-relaxed text-stone-700">
                            {{ milestone.description }}
                        </p>
                    </li>
                </ol>
                <SiteButton
                    :href="about.values.url()"
                    variant="outline"
                    class="mt-14"
                >
                    Our Vision, Mission &amp; Values
                </SiteButton>
            </div>
        </section>

        <CallToAction tone="sun" title="Be part of the next chapter">
            <p>
                CHICD welcomes people and organisations who share our commitment
                to improving lives through health innovation and community
                development.
            </p>
            <template #actions>
                <SiteButton :href="getInvolved.index.url()">
                    Get Involved
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
