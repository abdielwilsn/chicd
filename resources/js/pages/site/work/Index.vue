<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Newspaper } from '@lucide/vue';
import CallToAction from '@/components/site/CallToAction.vue';
import EmptyState from '@/components/site/EmptyState.vue';
import FeatureSplit from '@/components/site/FeatureSplit.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PostCard from '@/components/site/PostCard.vue';
import ProgrammeFeature from '@/components/site/ProgrammeFeature.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import {
    programmes as flagshipProgrammes,
    siteImages,
    thematicAreas,
} from '@/lib/siteContent';
import getInvolved from '@/routes/get-involved';
import resources from '@/routes/resources';
import work from '@/routes/work';
import type { AddedProgramme, Post } from '@/types';

defineProps<{
    /** Programmes added after the flagship ones. */
    programmes: AddedProgramme[];
    latestPosts: Post[];
}>();

const [surl, safeHousing] = flagshipProgrammes;
</script>

<template>
    <Head title="Our Work" />

    <div>
        <PageHeader
            :image="siteImages.education"
            eyebrow="Our Work"
            title="Improving Lives Through Innovation and Community Action"
            :breadcrumbs="[{ title: 'Our Work', href: work.index.url() }]"
        >
            <p>
                At the Centre for Health Innovation and Community Development
                (CHICD), our work responds to challenges affecting health,
                education, livelihoods, safety and community well-being.
            </p>
            <template #actions>
                <SiteButton href="#thematic-areas" variant="donate">
                    Thematic Areas
                </SiteButton>
                <SiteButton href="#programmes" variant="ghostLight">
                    Programmes
                </SiteButton>
            </template>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-10 lg:grid-cols-12 lg:gap-16">
                <p
                    class="font-heading text-[1.25rem] leading-snug font-semibold text-brand-900 sm:text-[1.5rem] lg:col-span-5 lg:text-[1.7rem]"
                >
                    We recognise that these challenges are often interconnected.
                </p>
                <div class="site-prose lg:col-span-7">
                    <p>
                        Poor health can affect a person's ability to learn or
                        earn a living, while limited economic opportunities,
                        inadequate housing and social exclusion can increase
                        vulnerability and restrict access to essential services.
                    </p>
                    <p>
                        Our work brings together research, innovation, community
                        participation and practical support to address these
                        challenges. We seek to understand the circumstances
                        affecting individuals and communities, identify
                        appropriate responses and develop interventions that
                        promote dignity, opportunity and sustainable
                        development.
                    </p>
                    <p>
                        CHICD organises its work around five thematic areas,
                        with specific programmes and projects developed to
                        respond to identified needs.
                    </p>
                </div>
            </div>
        </section>

        <section
            id="thematic-areas"
            class="scroll-mt-28 bg-cream py-20 sm:py-24"
        >
            <div class="site-container">
                <SectionIntro
                    eyebrow="Our Thematic Areas"
                    title="Five interconnected areas of work"
                />

                <nav aria-label="Thematic areas" class="mt-10">
                    <ol class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                        <li
                            v-for="area in thematicAreas"
                            :key="area.slug"
                            class="last:col-span-2 lg:last:col-span-1"
                        >
                            <a
                                :href="`#${area.slug}`"
                                class="block h-full border-t-[5px] bg-white px-3 py-3 transition-colors hover:bg-cream-deep sm:px-4 sm:py-4"
                                :class="area.tone.border"
                            >
                                <span
                                    class="font-display text-lg font-bold"
                                    :class="area.tone.text"
                                    >0{{ area.number }}</span
                                >
                                <span
                                    class="mt-1 block text-[0.95rem] leading-snug font-semibold text-ink sm:text-base"
                                    >{{ area.title }}</span
                                >
                            </a>
                        </li>
                    </ol>
                </nav>

                <div class="mt-16 space-y-16">
                    <article
                        v-for="area in thematicAreas"
                        :id="area.slug"
                        :key="area.slug"
                        class="grid scroll-mt-32 gap-8 lg:grid-cols-12 lg:gap-14"
                    >
                        <div class="lg:col-span-5">
                            <img
                                :src="area.image.src"
                                :alt="area.image.alt"
                                loading="lazy"
                                class="aspect-[4/3] w-full object-cover"
                            />
                            <div
                                class="flex items-baseline gap-4 px-6 py-5"
                                :class="area.tone.tile"
                            >
                                <span
                                    class="font-display text-4xl leading-none font-extrabold opacity-80"
                                    >0{{ area.number }}</span
                                >
                                <h2
                                    class="font-display text-[1.4rem] leading-tight font-bold uppercase"
                                >
                                    {{ area.title }}
                                </h2>
                            </div>
                        </div>
                        <div class="lg:col-span-7 lg:pt-2">
                            <h3
                                class="font-heading text-[1.4rem] leading-snug font-bold sm:text-[1.65rem] lg:text-[1.9rem]"
                                :class="area.tone.text"
                            >
                                {{ area.heading }}
                            </h3>
                            <div class="site-prose mt-5">
                                <p
                                    v-for="paragraph in area.paragraphs"
                                    :key="paragraph"
                                >
                                    {{ paragraph }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>

                <SiteButton href="#programmes" class="mt-16">
                    Explore Our Programmes
                </SiteButton>
            </div>
        </section>

        <section id="programmes" class="scroll-mt-28 py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Our Programmes and Initiatives"
                    title="Practical Responses to Community Needs"
                >
                    <p>
                        Our thematic areas provide the broader direction for
                        CHICD's work. Our programmes translate these priorities
                        into specific interventions that respond to identified
                        needs.
                    </p>
                    <p>
                        Each programme is developed around a defined purpose,
                        target population and approach to implementation. We
                        place importance on responsible programme management,
                        community participation and learning from experience.
                    </p>
                </SectionIntro>

                <div class="mt-12 space-y-6">
                    <FeatureSplit :image="surl.image" tone="cream">
                        <p
                            class="font-display text-[1rem] font-bold tracking-[0.12em] text-sun-700 uppercase"
                        >
                            Supporting Educational Participation and Dignity
                        </p>
                        <h3
                            class="mt-3 font-heading text-[1.6rem] leading-tight font-bold text-ink sm:text-[2.2rem]"
                        >
                            Show Up Ready to Learn (SURL)
                        </h3>
                        <div class="site-prose mt-5">
                            <p>
                                Show Up Ready to Learn (SURL) is CHICD's
                                education initiative designed to address
                                practical barriers that affect children's
                                participation in school, particularly in
                                underserved communities.
                            </p>
                            <p>
                                For some children, access to education is
                                affected not only by the availability of schools
                                but also by difficulties obtaining basic school
                                necessities. These challenges can affect
                                children's comfort, confidence and participation
                                in the school environment.
                            </p>
                            <p>
                                SURL seeks to respond to such barriers through
                                targeted educational support and community
                                engagement.
                            </p>
                        </div>
                        <div
                            class="mt-7 border-l-4 border-sun-400 bg-white p-5 sm:p-6"
                        >
                            <h4 class="font-heading text-xl font-bold text-ink">
                                Rural School Uniform Project
                            </h4>
                            <div
                                class="mt-2 space-y-2 leading-relaxed text-stone-700"
                            >
                                <p>
                                    The Rural School Uniform Project is an
                                    intervention under SURL that focuses on
                                    supporting children who lack adequate school
                                    uniforms.
                                </p>
                                <p>
                                    By helping address this practical need, the
                                    project seeks to promote dignity, reduce a
                                    material barrier to school participation and
                                    support children from disadvantaged
                                    backgrounds.
                                </p>
                                <p>
                                    The initiative also provides opportunities
                                    for collaboration with schools, communities,
                                    volunteers and individuals interested in
                                    supporting children's education.
                                </p>
                            </div>
                        </div>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <SiteButton :href="work.surl.url()">
                                Discover SURL
                            </SiteButton>
                            <SiteButton
                                :href="getInvolved.support.url()"
                                variant="outline"
                            >
                                Support Educational Access
                            </SiteButton>
                        </div>
                    </FeatureSplit>

                    <FeatureSplit
                        :image="safeHousing.image"
                        tone="brand"
                        reverse
                    >
                        <p
                            class="font-display text-[1rem] font-bold tracking-[0.12em] text-sun-300 uppercase"
                        >
                            Providing Safety, Shelter and Dignity
                        </p>
                        <h3
                            class="mt-3 font-heading text-[1.6rem] leading-tight font-bold text-white sm:text-[2.2rem]"
                        >
                            Safe Housing Programme for Vulnerable Adults
                        </h3>
                        <div class="mt-5 space-y-4 leading-[1.7]">
                            <p>
                                CHICD's Safe Housing Programme provides
                                temporary residential accommodation for
                                vulnerable adults who require a safe and
                                supportive place to stay.
                            </p>
                            <p>
                                The programme responds to circumstances in which
                                individuals may face housing insecurity or other
                                forms of vulnerability that make access to
                                suitable accommodation difficult.
                            </p>
                            <p>
                                Through a structured residential arrangement,
                                CHICD provides accommodation and basic housing
                                support while respecting residents' privacy,
                                independence and personal dignity.
                            </p>
                            <p>
                                The programme is intended for adults aged 18
                                years and above. Admission is subject to
                                eligibility and safety assessments, taking into
                                account individual circumstances and the
                                suitability of the available accommodation.
                            </p>
                            <p>
                                Residents are expected to manage their personal
                                care and medication needs independently. The
                                programme does not operate as a hospital,
                                clinical treatment facility or residential
                                medical care service.
                            </p>
                        </div>
                        <div
                            class="mt-7 space-y-6 border-l-4 border-sun-400 bg-brand-800 p-5 sm:p-6"
                        >
                            <div>
                                <h4
                                    class="font-heading text-xl font-bold text-white"
                                >
                                    Our Commitment to Residents
                                </h4>
                                <div class="mt-2 space-y-2 leading-relaxed">
                                    <p>
                                        The programme is guided by respect for
                                        individual dignity, confidentiality,
                                        personal autonomy and responsible
                                        safeguarding.
                                    </p>
                                    <p>
                                        We recognise that individuals
                                        experiencing vulnerability may have
                                        different circumstances and support
                                        needs. For this reason, admission and
                                        residential arrangements are managed
                                        through established procedures intended
                                        to promote safety and appropriate use of
                                        the facility.
                                    </p>
                                    <p>
                                        Information about residents and the
                                        residential facility is handled with
                                        particular attention to privacy and
                                        security.
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h4
                                    class="font-heading text-xl font-bold text-white"
                                >
                                    Enquiries and Support
                                </h4>
                                <div class="mt-2 space-y-2 leading-relaxed">
                                    <p>
                                        Individuals, organisations and relevant
                                        service providers may contact CHICD to
                                        enquire about the programme, discuss
                                        eligibility or explore appropriate forms
                                        of support and collaboration.
                                    </p>
                                    <p>
                                        For safety and confidentiality, the
                                        residential facility's location and
                                        residents' personal information are not
                                        publicly disclosed.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <SiteButton
                                :href="work.safeHousing.url()"
                                variant="light"
                            >
                                Learn About Safe Housing
                            </SiteButton>
                            <SiteButton
                                :href="`${work.safeHousing.url()}#enquiry`"
                                variant="ghostLight"
                            >
                                Make a Confidential Enquiry
                            </SiteButton>
                        </div>
                    </FeatureSplit>

                    <ProgrammeFeature
                        v-for="(programme, index) in programmes"
                        :key="programme.id"
                        :programme="programme"
                        :variant="index % 2 === 0 ? 'cream' : 'brand'"
                    />
                </div>
            </div>
        </section>

        <section class="border-t border-stone-200 py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Projects and Programme Updates"
                    title="Following Our Work in Communities"
                >
                    <p>
                        CHICD is committed to documenting its activities and
                        sharing information about the programmes and projects it
                        undertakes. This section provides updates on programme
                        implementation, community activities, emerging
                        initiatives and relevant developments across our
                        thematic areas.
                    </p>
                </SectionIntro>
                <template v-if="latestPosts.length">
                    <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <li v-for="post in latestPosts" :key="post.id">
                            <PostCard :post="post" />
                        </li>
                    </ul>
                    <SiteButton
                        :href="resources.news.url()"
                        variant="outline"
                        class="mt-10"
                    >
                        View All Updates
                    </SiteButton>
                </template>
                <EmptyState
                    v-else
                    :icon="Newspaper"
                    title="Programme updates will be shared here"
                    class="mt-10"
                >
                    <p>
                        As our work progresses, we will share verified
                        information about activities, lessons learned and
                        outcomes, while respecting the privacy and dignity of
                        the people involved.
                    </p>
                    <template #actions>
                        <SiteButton :href="resources.news.url()" variant="link">
                            View Programme Updates
                        </SiteButton>
                    </template>
                </EmptyState>
            </div>
        </section>

        <CallToAction
            eyebrow="Work With Us"
            title="Partnerships That Support Practical Solutions"
        >
            <p>
                Addressing health and development challenges requires the
                participation of communities, professionals, institutions and
                organisations with different forms of expertise and resources.
            </p>
            <p>
                Opportunities for collaboration may include programme
                implementation, research, technical assistance, training,
                resource mobilisation and community development initiatives. We
                are interested in partnerships that respond to genuine needs,
                promote responsible practice and contribute to meaningful
                improvements in people's lives.
            </p>
            <template #actions>
                <SiteButton
                    :href="getInvolved.partnerships.url()"
                    variant="light"
                >
                    Partner With CHICD
                </SiteButton>
                <SiteButton :href="getInvolved.support.url()" variant="donate">
                    Support Our Programmes
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
