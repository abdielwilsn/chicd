<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CallToAction from '@/components/site/CallToAction.vue';
import LinkCard from '@/components/site/LinkCard.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import PlusList from '@/components/site/PlusList.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SitePhoto from '@/components/site/SitePhoto.vue';
import { siteImages } from '@/lib/siteContent';
import { donate } from '@/routes';
import about from '@/routes/about';
import getInvolved from '@/routes/get-involved';
import work from '@/routes/work';

const aboutPages = [
    {
        title: 'Our Story',
        description:
            'How years of community public health service grew into an organised nonprofit.',
        href: about.story.url(),
        image: siteImages.story,
    },
    {
        title: 'Vision, Mission & Values',
        description:
            'What we aim to achieve and the values that guide how we work.',
        href: about.values.url(),
        image: siteImages.health,
    },
    {
        title: 'Leadership & Team',
        description:
            'The multidisciplinary organisation we are building and how it is led.',
        href: about.leadership.url(),
        image: siteImages.community,
    },
];

const approach = [
    {
        title: 'Understanding Problems Before Developing Solutions',
        paragraphs: [
            'At CHICD, we believe that effective interventions begin with a clear understanding of the problems they are intended to address.',
            'Communities experience challenges differently, and solutions should reflect their particular circumstances. We therefore seek to combine research evidence with local knowledge and the experiences of the people affected.',
            'Our approach involves identifying needs, engaging communities, examining available evidence, designing appropriate responses and assessing what changes as a result of our interventions.',
            'We recognise that some problems may require technology, while others may be better addressed through education, improved services, behavioural interventions, economic opportunities or changes in policy and practice. The nature of the problem should guide the response.',
        ],
    },
    {
        title: 'Research and Evidence',
        paragraphs: [
            'Research is an important part of how CHICD understands challenges and develops interventions.',
            'Our areas of interest include needs assessments, health and development research, surveys, data analysis, monitoring and evaluation, programme evaluations and evidence synthesis.',
            'We are particularly interested in research that contributes to practical decisions. Findings should help organisations and communities understand what needs to change, which interventions are appropriate, and how to improve existing approaches.',
        ],
    },
    {
        title: 'Community Participation',
        paragraphs: [
            'We believe people should have a meaningful role in identifying and addressing challenges that affect their lives.',
            'Community participation helps us understand local priorities, recognise existing strengths and develop interventions that are more responsive to the circumstances in which people live.',
            'We seek to work with communities rather than simply deliver activities to them.',
        ],
    },
    {
        title: 'Capacity Development',
        paragraphs: [
            'CHICD recognises that sustainable development relies partly on the knowledge, skills and capabilities of individuals and institutions.',
            "Through training, mentorship, technical assistance and other capacity development initiatives, we aim to strengthen people's ability to respond to challenges, pursue opportunities and contribute to their communities.",
        ],
    },
    {
        title: 'Advocacy and Policy Engagement',
        paragraphs: [
            'Some challenges cannot be adequately addressed by individual programmes alone.',
            'Where appropriate, CHICD seeks to use research findings, community experiences and programme evidence to contribute to discussions about policies, systems and practices affecting health and development.',
            'Our interest is in constructive engagement that supports informed decisions and practical improvements.',
        ],
    },
    {
        title: 'Partnerships',
        paragraphs: [
            'We recognise that no single organisation has all the expertise, resources or reach needed to address complex community challenges.',
            'CHICD values collaboration with communities, government institutions, academic institutions, research organisations, healthcare providers, civil society, businesses, foundations and development partners.',
            'We seek partnerships built around shared objectives, complementary expertise and a commitment to meaningful outcomes.',
        ],
    },
];

const peopleWeWorkWith = [
    'Children and young people',
    'Women and families',
    'Individuals with disabilities',
    'People living with health conditions',
    'Farmers',
    'Entrepreneurs',
    'Students',
    'Professionals',
    'Underserved populations',
];

const institutionsWeEngage = [
    'Community leaders',
    'Schools',
    'Healthcare facilities',
    'Universities',
    'Research institutions',
    'Government agencies',
    'Civil society organisations',
    'Development partners',
];
</script>

<template>
    <Head title="About Us" />

    <div>
        <PageHeader
            :image="siteImages.community"
            eyebrow="About CHICD"
            title="Innovating for Health, Empowering for Life."
            :breadcrumbs="[{ title: 'About Us', href: about.index.url() }]"
        >
            <p>
                The Centre for Health Innovation and Community Development
                (CHICD) is a Nigerian nonprofit organisation committed to
                improving lives through health innovation, research and
                sustainable community development.
            </p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-7">
                    <SectionIntro title="Who We Are" />
                    <div class="site-prose mt-6">
                        <p>
                            We bring together research, technology, behavioural
                            approaches and community action to develop practical
                            responses to health and development challenges. Our
                            interests extend beyond healthcare to the conditions
                            that influence people's well-being, including
                            education, livelihoods, social inclusion, access to
                            technology and the environment in which communities
                            live.
                        </p>
                        <p>
                            We recognise that people and communities face
                            different challenges. An intervention that works in
                            one setting may not be appropriate in another. For
                            this reason, we place importance on understanding
                            local needs, listening to the people affected and
                            drawing on reliable evidence before deciding how
                            best to respond.
                        </p>
                    </div>
                    <p
                        class="mt-8 border-l-4 border-sun-400 pl-6 font-heading text-[1.15rem] leading-snug font-semibold text-brand-900 sm:text-[1.35rem]"
                    >
                        CHICD works with communities, institutions and
                        development partners to explore solutions that are
                        practical, inclusive and capable of producing meaningful
                        improvements in people's lives.
                    </p>
                </div>
                <div class="lg:col-span-5">
                    <SitePhoto
                        :image="siteImages.livelihoods"
                        offset="sun"
                        image-class="aspect-[4/5]"
                    />
                </div>
            </div>

            <div class="site-container mt-20">
                <h2 class="site-label">More about CHICD</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <LinkCard
                        v-for="page in aboutPages"
                        :key="page.title"
                        :href="page.href"
                        :title="page.title"
                        :description="page.description"
                        :image="page.image"
                    />
                </div>
            </div>
        </section>

        <section id="approach" class="scroll-mt-28 bg-cream py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="Our Approach">
                    <p>
                        We believe meaningful development begins with
                        understanding the people and circumstances behind the
                        challenges being addressed.
                    </p>
                </SectionIntro>
                <ol class="mt-14 border-t border-stone-300">
                    <li
                        v-for="(item, index) in approach"
                        :key="item.title"
                        class="grid gap-6 border-b border-stone-300 py-10 lg:grid-cols-12 lg:gap-12"
                    >
                        <div class="lg:col-span-4">
                            <span
                                class="font-display text-4xl leading-none font-extrabold text-sun-600"
                                >0{{ index + 1 }}</span
                            >
                            <h3
                                class="mt-3 font-heading text-[1.5rem] leading-snug font-bold text-ink"
                            >
                                {{ item.title }}
                            </h3>
                        </div>
                        <div class="site-prose lg:col-span-8">
                            <p
                                v-for="paragraph in item.paragraphs"
                                :key="paragraph"
                            >
                                {{ paragraph }}
                            </p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section class="py-20 sm:py-24">
            <div class="site-container">
                <SectionIntro title="Who We Work With">
                    <p>
                        CHICD's work particularly focuses on individuals and
                        communities facing barriers to essential services,
                        knowledge and opportunities.
                    </p>
                </SectionIntro>
                <div class="mt-12 grid gap-10 md:grid-cols-2 lg:gap-16">
                    <div class="border-t-[5px] border-brand-600 pt-6">
                        <h3 class="font-heading text-xl font-bold text-ink">
                            People we seek to work with
                        </h3>
                        <p class="mt-1 mb-5 text-stone-600">
                            Depending on the programme and the needs being
                            addressed.
                        </p>
                        <PlusList :items="peopleWeWorkWith" :columns="2" />
                    </div>
                    <div class="border-t-[5px] border-sun-400 pt-6">
                        <h3 class="font-heading text-xl font-bold text-ink">
                            Institutions we engage with
                        </h3>
                        <p class="mt-1 mb-5 text-stone-600">
                            Partners who help us reach and serve communities.
                        </p>
                        <PlusList :items="institutionsWeEngage" :columns="2" />
                    </div>
                </div>
                <p
                    class="mt-14 max-w-3xl font-heading text-[1.15rem] leading-snug font-semibold text-brand-900 sm:text-[1.35rem]"
                >
                    We recognise that communities possess knowledge,
                    capabilities and experiences that should inform the
                    interventions designed to support them.
                </p>
            </div>
        </section>

        <CallToAction title="Building Stronger Communities Together">
            <p>
                The challenges affecting health and development rarely exist in
                isolation. Addressing them requires careful understanding,
                practical ideas, appropriate resources and people willing to
                work together.
            </p>
            <p>
                CHICD welcomes opportunities to collaborate on research,
                community interventions, innovation, capacity development and
                initiatives that improve the lives of underserved populations.
            </p>
            <p>
                Our ambition is to build an organisation capable of translating
                knowledge into action, responding to real community needs and
                contributing to improvements that can endure.
            </p>
            <template #actions>
                <SiteButton
                    :href="getInvolved.partnerships.url()"
                    variant="light"
                >
                    Partner With Us
                </SiteButton>
                <SiteButton :href="work.index.url()" variant="ghostLight">
                    Explore Our Work
                </SiteButton>
                <!-- <SiteButton :href="donate.url()" variant="donate">
                    Support CHICD
                </SiteButton> -->
            </template>
        </CallToAction>
    </div>
</template>
