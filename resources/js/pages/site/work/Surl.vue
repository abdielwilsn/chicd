<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CallToAction from '@/components/site/CallToAction.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SitePhoto from '@/components/site/SitePhoto.vue';
import { siteImages, surlProjects } from '@/lib/siteContent';
import { donate } from '@/routes';
import getInvolved from '@/routes/get-involved';
import work from '@/routes/work';

const donateToSurl = donate.url({ query: { fund: 'surl' } });
</script>

<template>
    <Head title="Show Up Ready to Learn (SURL)" />

    <div>
        <PageHeader
            :image="siteImages.surl"
            eyebrow="Show Up Ready to Learn (SURL)"
            title="Every Child Deserves to Show Up Ready to Learn"
            :breadcrumbs="[
                { title: 'Our Work', href: work.index.url() },
                { title: 'SURL', href: work.surl.url() },
            ]"
        >
            <p>
                Show Up Ready to Learn (SURL) is an education initiative of the
                Centre for Health Innovation and Community Development (CHICD),
                dedicated to removing practical barriers to education and
                helping children participate in school with dignity, confidence
                and the resources they need.
            </p>
            <template #actions>
                <SiteButton :href="donateToSurl" variant="donate" size="lg">
                    Support SURL
                </SiteButton>
                <SiteButton href="#projects" variant="ghostLight" size="lg">
                    Explore Our Projects
                </SiteButton>
            </template>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div
                class="site-container grid items-center gap-14 lg:grid-cols-12 lg:gap-16"
            >
                <div class="lg:col-span-6">
                    <SectionIntro
                        eyebrow="Why SURL"
                        title="Removing practical barriers to school"
                    >
                        <p>
                            For some children, access to education is affected
                            not only by the availability of schools but also by
                            difficulties obtaining basic school necessities.
                            These challenges can affect children's comfort,
                            confidence and participation in the school
                            environment.
                        </p>
                        <p>
                            SURL seeks to respond to such barriers through
                            targeted educational support and community
                            engagement.
                        </p>
                    </SectionIntro>
                    <figure class="mt-9 border-l-4 border-sun-400 pl-6">
                        <blockquote
                            class="font-heading text-[1.15rem] leading-snug font-semibold text-brand-900 sm:text-[1.3rem]"
                        >
                            By helping address this practical need, the project
                            seeks to promote dignity, reduce a material barrier
                            to school participation and support children from
                            disadvantaged backgrounds.
                        </blockquote>
                        <figcaption
                            class="mt-3 font-display font-bold tracking-[0.08em] text-sun-700 uppercase"
                        >
                            Rural School Uniform Project
                        </figcaption>
                    </figure>
                </div>
                <div class="lg:col-span-6">
                    <SitePhoto
                        :image="siteImages.surlReading"
                        offset="sun"
                        image-class="aspect-[4/3]"
                    />
                </div>
            </div>
        </section>

        <section id="projects" class="scroll-mt-28 bg-cream py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Our Projects"
                    title="How SURL helps children learn"
                />
                <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <li
                        v-for="(project, index) in surlProjects"
                        :key="project.title"
                        class="flex flex-col border-t-[5px] border-sun-400 bg-white p-7"
                    >
                        <div class="flex items-center justify-between">
                            <component
                                :is="project.icon"
                                class="size-8 text-brand-700"
                                stroke-width="1.6"
                            />
                            <span
                                class="font-display text-2xl font-extrabold text-sun-600"
                                >0{{ index + 1 }}</span
                            >
                        </div>
                        <h3
                            class="mt-5 font-heading text-xl leading-snug font-bold text-ink"
                        >
                            {{ project.title }}
                        </h3>
                        <p class="mt-2 leading-relaxed text-stone-700">
                            {{ project.description }}
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <CallToAction tone="sun" title="Help a Child Show Up Ready to Learn">
            <p>
                You can support SURL by sponsoring school necessities, making a
                donation, volunteering or partnering with CHICD.
            </p>
            <template #actions>
                <SiteButton :href="donateToSurl">Donate to SURL</SiteButton>
                <SiteButton
                    :href="getInvolved.partnerships.url()"
                    variant="outline"
                >
                    Partner With Us
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
