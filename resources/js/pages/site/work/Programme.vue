<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CallToAction from '@/components/site/CallToAction.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import SitePhoto from '@/components/site/SitePhoto.vue';
import { donate } from '@/routes';
import getInvolved from '@/routes/get-involved';
import work from '@/routes/work';
import type { AddedProgramme } from '@/types';

defineProps<{
    programme: AddedProgramme;
}>();
</script>

<template>
    <Head :title="programme.name">
        <meta name="description" :content="programme.summary[0]" />
    </Head>

    <div>
        <PageHeader
            :image="programme.image"
            eyebrow="Our Programmes"
            :title="programme.name"
            :breadcrumbs="[
                { title: 'Our Work', href: work.index.url() },
                { title: programme.name, href: programme.url },
            ]"
        >
            <p v-if="programme.tagline">{{ programme.tagline }}</p>
        </PageHeader>

        <section class="py-20 sm:py-24">
            <div class="site-container grid gap-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-7">
                    <div class="site-prose">
                        <p
                            v-for="paragraph in programme.summary"
                            :key="paragraph"
                            class="font-heading text-[1.2rem] leading-snug font-semibold text-brand-900 sm:text-[1.35rem]"
                        >
                            {{ paragraph }}
                        </p>
                        <p v-for="paragraph in programme.body" :key="paragraph">
                            {{ paragraph }}
                        </p>
                    </div>
                    <SiteButton
                        :href="`${work.index.url()}#programmes`"
                        variant="outline"
                        class="mt-10"
                    >
                        See All Programmes
                    </SiteButton>
                </div>
                <div class="lg:col-span-5 lg:pt-2">
                    <SitePhoto
                        :image="programme.image"
                        offset="sun"
                        image-class="aspect-[4/5]"
                    />
                </div>
            </div>
        </section>

        <CallToAction
            eyebrow="Get Involved"
            :title="`Support ${programme.name}`"
        >
            <p>
                Your contribution, expertise or partnership can help this
                programme reach more people.
            </p>
            <template #actions>
                <SiteButton :href="donate.url()" variant="donate">
                    Donate
                </SiteButton>
                <SiteButton
                    :href="getInvolved.partnerships.url()"
                    variant="ghostLight"
                >
                    Partner With Us
                </SiteButton>
            </template>
        </CallToAction>
    </div>
</template>
