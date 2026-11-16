<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import NewsletterForm from '@/components/site/NewsletterForm.vue';
import PlusList from '@/components/site/PlusList.vue';
import SectionIntro from '@/components/site/SectionIntro.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SitePhoto from '@/components/site/SitePhoto.vue';
import SocialLinks from '@/components/site/SocialLinks.vue';
import ThematicAreaCard from '@/components/site/ThematicAreaCard.vue';
import FeatureSplit from '@/components/site/FeatureSplit.vue';
import { programmes, siteImages, thematicAreas } from '@/lib/siteContent';
import { donate } from '@/routes';
import about from '@/routes/about';
import getInvolved from '@/routes/get-involved';
import resources from '@/routes/resources';
import work from '@/routes/work';

const page = usePage();
const hasSocialLinks = computed(
    () => Object.keys(page.props.site.social).length > 0,
);

const atAGlance = [
    {
        value: '7',
        unit: 'years',
        label: 'Around seven years of community public health service before CHICD was registered',
    },
    {
        value: '2',
        unit: 'states',
        label: 'Akwa Ibom and Ekiti, where our founding experience was shaped',
    },
    {
        value: '5',
        unit: 'areas',
        label: 'Interconnected thematic areas across health and development',
    },
    {
        value: '2',
        unit: 'programmes',
        label: 'Show Up Ready to Learn (SURL) and the Safe Housing Programme',
    },
];

const approachSteps = [
    'Identify needs',
    'Engage communities',
    'Examine evidence',
    'Design responses',
    'Assess what changes',
];

const researchInterests = [
    'Public health',
    'Behavioural science',
    'Mental well-being',
    'Education',
    'Social inclusion',
    'Community development',
    'Technology for health and development',
];

const partnerTypes = [
    'Government agencies',
    'Universities',
    'Research institutions',
    'Healthcare providers',
    'Civil society organisations',
    'Businesses',
    'Foundations',
    'Development partners',
];

const [surl, safeHousing] = programmes;
</script>

<template>
    <Head title="Innovating for Health, Empowering for Life">
        <meta
            name="description"
            content="CHICD is a Nigerian nonprofit improving lives through health innovation, research and inclusive community development."
        />
    </Head>

    <div>
        <!-- 1. Hero -->
        <section class="bg-brand-900 lg:grid lg:min-h-[42rem] lg:grid-cols-12">
            <div
                class="relative min-h-[19rem] sm:min-h-[26rem] lg:order-2 lg:col-span-7"
            >
                <img
                    :src="siteImages.hero.src"
                    :alt="siteImages.hero.alt"
                    class="absolute inset-0 size-full object-cover object-[50%_22%]"
                    fetchpriority="high"
                />
            </div>
            <div
                class="relative flex items-center border-t-[6px] border-sun-400 px-5 py-12 text-white sm:px-10 sm:py-16 lg:col-span-5 lg:border-t-0 lg:py-20 lg:pr-12 lg:pl-[max(2rem,calc((100vw-80rem)/2+2rem))]"
            >
                <div
                    class="absolute inset-y-0 right-0 hidden w-1.5 bg-sun-400 lg:block"
                    aria-hidden="true"
                />
                <div>
                    <p
                        class="font-display text-[1rem] font-bold tracking-[0.14em] text-sun-300 uppercase"
                    >
                        Centre for Health Innovation and Community Development
                    </p>
                    <h1
                        class="mt-5 font-display text-[2.5rem] leading-[0.95] font-extrabold uppercase sm:text-[3.75rem] xl:text-[4.3rem]"
                    >
                        Advancing Health Innovation.
                        <span class="site-mark"
                            >Strengthening Communities.</span
                        >
                        Creating Opportunities.
                    </h1>
                    <div
                        class="mt-7 space-y-3 text-[1.0625rem] leading-relaxed text-brand-100"
                    >
                        <p>
                            At the Centre for Health Innovation and Community
                            Development (CHICD), we bring together research,
                            technology, behavioural science and community action
                            to address health and development challenges.
                        </p>
                        <p>
                            We are committed to practical, inclusive solutions
                            that improve well-being, expand opportunities and
                            strengthen communities.
                        </p>
                    </div>
                    <div
                        class="mt-9 flex flex-col gap-3 sm:flex-row sm:flex-wrap"
                    >
                        <SiteButton
                            :href="work.index.url()"
                            variant="donate"
                            size="lg"
                        >
                            Explore Our Work
                        </SiteButton>
                        <SiteButton
                            :href="donate.url()"
                            variant="ghostLight"
                            size="lg"
                        >
                            Support CHICD
                        </SiteButton>
                    </div>
                </div>
            </div>
        </section>

        <!-- At a glance -->
        <section class="border-b border-stone-200 bg-white">
            <div class="site-container py-12 sm:py-14">
                <h2 class="site-label">CHICD at a glance</h2>
                <ul
                    class="mt-8 grid grid-cols-2 gap-y-10 lg:grid-cols-4 lg:divide-x lg:divide-stone-200"
                >
                    <li
                        v-for="(fact, index) in atAGlance"
                        :key="fact.label"
                        class="pr-6"
                        :class="{ 'lg:pl-8': index > 0 }"
                    >
                        <p class="flex flex-wrap items-baseline gap-x-2">
                            <span
                                class="font-display text-5xl leading-none font-extrabold text-brand-700 sm:text-6xl"
                                >{{ fact.value }}</span
                            >
                            <span
                                class="font-display text-lg font-bold tracking-[0.06em] text-sun-600 uppercase sm:text-xl"
                                >{{ fact.unit }}</span
                            >
                        </p>
                        <p
                            class="mt-3 max-w-[16rem] leading-snug text-stone-700"
                        >
                            {{ fact.label }}
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <!-- 2. Welcome -->
        <section class="bg-cream py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-7">
                    <SectionIntro
                        eyebrow="Welcome to CHICD"
                        title="Where Innovation Meets Community Development"
                    />
                    <p
                        class="mt-8 font-heading text-[1.25rem] leading-snug font-semibold text-brand-900 sm:text-[1.5rem] lg:text-[1.7rem]"
                    >
                        CHICD is a Nigerian nonprofit organisation dedicated to
                        improving lives through health innovation, research and
                        inclusive community development.
                    </p>
                    <div class="site-prose mt-6">
                        <p>
                            We recognise that health and development are closely
                            connected. Access to healthcare matters, but so do
                            education, economic opportunities, social inclusion
                            and the conditions in which people live.
                        </p>
                        <p>
                            Our work is informed by research, local knowledge
                            and the experiences of communities. We seek to
                            understand the challenges people face and develop
                            responses that are practical, appropriate and
                            capable of making a meaningful difference.
                        </p>
                    </div>
                    <SiteButton
                        :href="about.index.url()"
                        variant="outline"
                        class="mt-9"
                    >
                        Learn More About CHICD
                    </SiteButton>
                </div>
                <div class="lg:col-span-5 lg:pt-4">
                    <SitePhoto
                        :image="siteImages.community"
                        offset="sun"
                        image-class="aspect-[4/5]"
                    />
                    <blockquote
                        class="mt-10 border-l-4 border-brand-700 pl-6 font-heading text-[1.2rem] leading-relaxed text-brand-950 italic"
                    >
                        From supporting vulnerable individuals to exploring
                        innovative approaches to health and development, our
                        commitment is to connect knowledge, resources and
                        community participation with purposeful action.
                    </blockquote>
                </div>
            </div>
        </section>

        <!-- 3. Areas of Work -->
        <section class="py-20 sm:py-24">
            <div class="site-container">
                <div
                    class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between"
                >
                    <SectionIntro
                        eyebrow="Our Areas of Work"
                        title="Working Across Health and Community Development"
                    >
                        <p>
                            Our work is organised around five interconnected
                            thematic areas that reflect the relationship between
                            health, opportunity and sustainable community
                            development.
                        </p>
                    </SectionIntro>
                    <SiteButton
                        :href="work.index.url()"
                        variant="outline"
                        class="self-start lg:self-auto"
                    >
                        Explore Our Areas of Work
                    </SiteButton>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-6">
                    <ThematicAreaCard
                        v-for="(area, index) in thematicAreas"
                        :key="area.slug"
                        :area="area"
                        :class="index < 3 ? 'lg:col-span-2' : 'lg:col-span-3'"
                    />
                </div>
            </div>
        </section>

        <!-- 4. Programmes -->
        <section class="pb-20 sm:pb-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Our Programmes and Initiatives"
                    title="Responding to Needs. Creating Opportunities. Protecting Dignity."
                >
                    <p>
                        CHICD develops and implements programmes that respond to
                        practical challenges affecting education, safety and
                        community well-being. Our initiatives seek to reduce
                        barriers to essential opportunities and provide support
                        that respects individual dignity.
                    </p>
                </SectionIntro>

                <div class="mt-12 space-y-6">
                    <FeatureSplit :image="surl.image" tone="cream">
                        <p
                            class="font-display text-[1rem] font-bold tracking-[0.12em] text-sun-700 uppercase"
                        >
                            {{ surl.tagline }}
                        </p>
                        <h3
                            class="mt-3 font-heading text-[1.6rem] leading-tight font-bold text-ink sm:text-[2.2rem]"
                        >
                            {{ surl.name }}
                        </h3>
                        <div class="site-prose mt-5">
                            <p
                                v-for="paragraph in surl.summary"
                                :key="paragraph"
                            >
                                {{ paragraph }}
                            </p>
                        </div>
                        <SiteButton
                            :href="surl.href"
                            variant="link"
                            class="mt-7 self-start"
                        >
                            {{ surl.cta }}
                        </SiteButton>
                    </FeatureSplit>

                    <FeatureSplit
                        :image="safeHousing.image"
                        tone="brand"
                        reverse
                    >
                        <p
                            class="font-display text-[1rem] font-bold tracking-[0.12em] text-sun-300 uppercase"
                        >
                            {{ safeHousing.tagline }}
                        </p>
                        <h3
                            class="mt-3 font-heading text-[1.6rem] leading-tight font-bold text-white sm:text-[2.2rem]"
                        >
                            {{ safeHousing.name }}
                        </h3>
                        <div class="mt-5 space-y-4 leading-[1.7]">
                            <p
                                v-for="paragraph in safeHousing.summary"
                                :key="paragraph"
                            >
                                {{ paragraph }}
                            </p>
                        </div>
                        <SiteButton
                            :href="safeHousing.href"
                            variant="linkLight"
                            class="mt-7 self-start"
                        >
                            {{ safeHousing.cta }}
                        </SiteButton>
                    </FeatureSplit>
                </div>

                <div class="mt-10">
                    <SiteButton :href="`${work.index.url()}#programmes`">
                        View All Programmes
                    </SiteButton>
                </div>
            </div>
        </section>

        <!-- 5. Research, Innovation and Knowledge -->
        <section class="bg-cream py-20 sm:py-24">
            <div
                class="site-container grid items-center gap-14 lg:grid-cols-12 lg:gap-16"
            >
                <div class="lg:col-span-5">
                    <SitePhoto
                        :image="siteImages.research"
                        offset="brand"
                        image-class="aspect-[4/5]"
                    />
                </div>
                <div class="lg:col-span-7">
                    <SectionIntro
                        eyebrow="Research, Innovation and Knowledge"
                        title="Turning Knowledge Into Practical Solutions"
                    >
                        <p>
                            Research helps us understand community challenges,
                            identify appropriate responses and learn from the
                            outcomes of our interventions.
                        </p>
                        <p>
                            At CHICD, we are interested in research that goes
                            beyond describing problems to informing decisions
                            and improving practice.
                        </p>
                        <p>
                            We seek to work with researchers, academic
                            institutions, practitioners and development partners
                            to generate useful evidence, strengthen programme
                            design and contribute to informed decision-making.
                        </p>
                    </SectionIntro>
                    <h3
                        class="mt-9 font-display text-lg font-bold tracking-[0.08em] text-brand-800 uppercase"
                    >
                        Our research interests
                    </h3>
                    <PlusList
                        :items="researchInterests"
                        :columns="2"
                        class="mt-4"
                    />
                    <p class="mt-8 leading-relaxed text-stone-700">
                        Through research, innovation and knowledge sharing, we
                        aim to connect scientific understanding with the
                        everyday realities of the communities we serve.
                    </p>
                    <SiteButton
                        :href="resources.research.url()"
                        variant="outline"
                        class="mt-8"
                    >
                        Explore Research &amp; Insights
                    </SiteButton>
                </div>
            </div>
        </section>

        <!-- 6. Our Approach -->
        <section class="bg-brand-900 py-20 text-white sm:py-24">
            <div class="site-container">
                <SectionIntro
                    eyebrow="Our Approach"
                    title="Understanding Communities. Developing Appropriate Solutions."
                    inverted
                >
                    <p>
                        We believe meaningful development begins with
                        understanding the people and circumstances behind the
                        challenges being addressed.
                    </p>
                </SectionIntro>
                <ol
                    class="mt-10 grid gap-px bg-white/15 sm:mt-14 sm:grid-cols-2 lg:grid-cols-5"
                >
                    <li
                        v-for="(step, index) in approachSteps"
                        :key="step"
                        class="flex items-center gap-5 bg-brand-900 py-4 pr-6 sm:block sm:py-6 sm:pl-6 lg:first:pl-0"
                    >
                        <span
                            class="w-11 shrink-0 font-display text-4xl leading-none font-extrabold text-sun-400 sm:w-auto sm:text-5xl"
                            >0{{ index + 1 }}</span
                        >
                        <p
                            class="font-display text-[1.25rem] leading-tight font-bold tracking-[0.04em] uppercase sm:mt-3 sm:text-[1.35rem]"
                        >
                            {{ step }}
                        </p>
                    </li>
                </ol>
                <div
                    class="mt-12 grid gap-6 leading-[1.7] text-brand-100 lg:grid-cols-2 lg:gap-12"
                >
                    <p>
                        Our approach brings together research, community
                        participation, innovation, capacity development and
                        partnerships. We recognise that different problems
                        require different responses, and that effective
                        solutions must reflect local realities.
                    </p>
                    <p>
                        We seek to develop interventions that are informed by
                        evidence, responsive to community priorities and open to
                        learning and improvement.
                    </p>
                </div>
                <SiteButton
                    :href="`${about.index.url()}#approach`"
                    variant="donate"
                    class="mt-10"
                >
                    Discover Our Approach
                </SiteButton>
            </div>
        </section>

        <!-- 7. Partnerships and Collaboration -->
        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-6">
                    <SectionIntro
                        eyebrow="Partnerships and Collaboration"
                        title="Working Together to Make a Difference"
                    >
                        <p>
                            Improving health and strengthening communities
                            requires collaboration across disciplines,
                            institutions and sectors.
                        </p>
                    </SectionIntro>
                    <h3
                        class="mt-9 font-display text-lg font-bold tracking-[0.08em] text-brand-800 uppercase"
                    >
                        CHICD welcomes partnerships with
                    </h3>
                    <PlusList :items="partnerTypes" :columns="2" class="mt-4" />
                    <div class="site-prose mt-8">
                        <p>
                            We are interested in collaborations that advance
                            community programmes, research, health innovation,
                            capacity development and sustainable solutions to
                            development challenges.
                        </p>
                        <p>
                            Whether through technical expertise, programme
                            support, research collaboration or shared resources,
                            we believe partnerships can help translate promising
                            ideas into meaningful action.
                        </p>
                    </div>
                    <SiteButton
                        :href="getInvolved.partnerships.url()"
                        class="mt-9"
                    >
                        Partner With Us
                    </SiteButton>
                </div>
                <div class="lg:col-span-6 lg:pt-16">
                    <SitePhoto
                        :image="siteImages.training"
                        offset="cream"
                        image-class="aspect-[5/4]"
                    />
                </div>
            </div>
        </section>

        <!-- 8. Support Our Mission -->
        <section class="relative isolate overflow-hidden py-20 sm:py-28">
            <img
                :src="siteImages.surlReading.src"
                alt=""
                loading="lazy"
                class="absolute inset-0 -z-20 size-full object-cover"
            />
            <div class="absolute inset-0 -z-10 bg-brand-950/75" />
            <div class="site-container">
                <div class="max-w-3xl text-white">
                    <p class="site-label text-sun-300">Support Our Mission</p>
                    <h2 class="site-heading mt-5 text-white">
                        Your Support Can Help Change Lives
                    </h2>
                    <div
                        class="mt-5 space-y-4 text-[1.0625rem] leading-[1.7] text-brand-100 sm:text-lg"
                    >
                        <p>
                            Behind many health and development challenges are
                            individuals and families who need practical support,
                            access to opportunities and systems that respond to
                            their circumstances.
                        </p>
                        <p>
                            By supporting CHICD, you can contribute to
                            initiatives that address barriers to healthcare,
                            education, safety and community well-being.
                        </p>
                    </div>
                </div>
                <div class="mt-12 grid gap-4 md:grid-cols-3">
                    <div class="flex flex-col bg-sun-400 p-8 text-brand-950">
                        <h3
                            class="font-display text-[2rem] leading-none font-extrabold uppercase"
                        >
                            Donate
                        </h3>
                        <p class="mt-4 flex-1 leading-relaxed">
                            Your contribution can support our programmes,
                            including educational assistance through SURL and
                            temporary housing support for vulnerable adults, as
                            well as other community development initiatives.
                        </p>
                        <SiteButton
                            :href="donate.url()"
                            class="mt-7 self-start"
                        >
                            Donate Now
                        </SiteButton>
                    </div>
                    <div class="flex flex-col bg-white p-8 text-ink">
                        <h3
                            class="font-display text-[2rem] leading-none font-extrabold text-brand-800 uppercase"
                        >
                            Volunteer
                        </h3>
                        <p class="mt-4 flex-1 leading-relaxed text-stone-700">
                            You can also make a difference by volunteering your
                            time or contributing professional expertise to our
                            programmes.
                        </p>
                        <SiteButton
                            :href="getInvolved.index.url()"
                            variant="outline"
                            class="mt-7 self-start"
                        >
                            Get Involved
                        </SiteButton>
                    </div>
                    <div class="flex flex-col bg-brand-700 p-8 text-white">
                        <h3
                            class="font-display text-[2rem] leading-none font-extrabold uppercase"
                        >
                            Partner
                        </h3>
                        <p class="mt-4 flex-1 leading-relaxed text-brand-100">
                            Collaborate with us on research, community
                            interventions, innovation, capacity development and
                            initiatives that improve lives.
                        </p>
                        <SiteButton
                            :href="getInvolved.partnerships.url()"
                            variant="light"
                            class="mt-7 self-start"
                        >
                            Partner With Us
                        </SiteButton>
                    </div>
                </div>
            </div>
        </section>

        <!-- 9. Stay Connected -->
        <section class="bg-sun-400">
            <div
                class="site-container grid gap-8 py-14 sm:py-16 lg:grid-cols-12 lg:items-center lg:gap-12"
            >
                <div class="lg:col-span-5">
                    <p class="site-label text-brand-900">Stay Connected</p>
                    <h2
                        class="mt-4 font-heading text-[1.6rem] leading-tight font-bold text-brand-950 sm:text-[1.9rem]"
                    >
                        Follow Our Work and Be Part of Our Community
                    </h2>
                    <p class="mt-3 leading-relaxed text-brand-950/85">
                        Stay informed about CHICD's programmes, research,
                        community activities, opportunities and organisational
                        updates. Follow our journey as we work to advance health
                        innovation and community development.
                    </p>
                </div>
                <div class="lg:col-span-7">
                    <NewsletterForm />
                    <div
                        v-if="hasSocialLinks"
                        class="mt-6 flex items-center gap-4"
                    >
                        <span
                            class="font-display text-lg font-bold tracking-[0.06em] text-brand-950 uppercase"
                            >Follow CHICD</span
                        >
                        <SocialLinks />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
