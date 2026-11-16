<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CallToAction from '@/components/site/CallToAction.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { programmes, siteImages } from '@/lib/siteContent';
import { donate } from '@/routes';
import getInvolved from '@/routes/get-involved';

const supportOptions = programmes.map((programme, index) => ({
    ...programme,
    shortName: index === 0 ? 'SURL' : 'Safe Housing',
    accent: index === 0 ? 'border-sun-400' : 'border-brand-600',
    donateHref: donate.url({ query: { fund: programme.slug } }),
}));
</script>

<template>
    <Head title="Support Our Programmes" />

    <div>
        <PageHeader
            :image="siteImages.surlReading"
            eyebrow="Support Our Programmes"
            title="Support a Programme"
            :breadcrumbs="[
                { title: 'Get Involved', href: getInvolved.index.url() },
                {
                    title: 'Support Our Programmes',
                    href: getInvolved.support.url(),
                },
            ]"
        >
            <p>
                Contribute to initiatives such as Show Up Ready to Learn (SURL)
                and the Safe Housing Programme. You can also make a difference
                by volunteering, contributing professional expertise or
                partnering with us.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Choose a programme"
                    title="Where your support goes"
                />
                <div class="mt-12 grid gap-6 lg:grid-cols-2">
                    <article
                        v-for="programme in supportOptions"
                        :key="programme.slug"
                        class="flex flex-col border-t-[5px] bg-cream/60"
                        :class="programme.accent"
                    >
                        <img
                            :src="programme.image.src"
                            :alt="programme.image.alt"
                            loading="lazy"
                            class="aspect-[16/9] w-full object-cover"
                        />
                        <div class="flex flex-1 flex-col p-8 sm:p-10">
                            <p
                                class="font-display text-[1rem] font-bold tracking-[0.12em] text-brand-700 uppercase"
                            >
                                {{ programme.tagline }}
                            </p>
                            <h2
                                class="mt-2 font-heading text-[1.7rem] leading-tight font-bold text-ink"
                            >
                                {{ programme.name }}
                            </h2>
                            <p
                                class="mt-4 flex-1 leading-relaxed text-stone-700"
                            >
                                {{ programme.summary[0] }}
                            </p>
                            <div class="mt-8 flex flex-wrap items-center gap-6">
                                <SiteButton
                                    :href="programme.donateHref"
                                    variant="donate"
                                >
                                    Donate to {{ programme.shortName }}
                                </SiteButton>
                                <SiteButton
                                    :href="programme.href"
                                    variant="link"
                                >
                                    Learn more
                                </SiteButton>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <CallToAction title="Not sure where to give?">
            <p>
                A general donation lets CHICD direct your support to where it is
                needed most across our programmes and community development
                initiatives.
            </p>
            <template #actions>
                <SiteButton :href="donate.url()" variant="donate">
                    Donate to CHICD
                </SiteButton>
                <SiteButton :href="getInvolved.volunteer.url()" variant="light">
                    Volunteer instead
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
