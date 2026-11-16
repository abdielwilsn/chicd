<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { CircleCheck, Lock } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import SiteButton from '@/components/site/SiteButton.vue';
import { store } from '@/routes/enquiries';

export type EnquiryType =
    | 'general'
    | 'partnership'
    | 'volunteer'
    | 'research'
    | 'donation'
    | 'safe_housing'
    | 'feedback';

type Props = {
    type: EnquiryType;
    /** Shows a select with these subjects instead of a free-text subject field. */
    subjectOptions?: string[];
    subjectLabel?: string;
    /** Pre-selected subject (for example, the programme a donor chose). */
    defaultSubject?: string;
    showSubject?: boolean;
    showOrganisation?: boolean;
    showPhone?: boolean;
    /** Allow either an email address or a phone number instead of requiring email. */
    emailOrPhone?: boolean;
    nameLabel?: string;
    nameHint?: string;
    messageLabel?: string;
    messagePlaceholder?: string;
    submitLabel?: string;
    confidential?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    subjectOptions: () => [],
    subjectLabel: 'Subject',
    defaultSubject: '',
    showSubject: true,
    showOrganisation: false,
    showPhone: true,
    emailOrPhone: false,
    nameLabel: 'Full name',
    nameHint: undefined,
    messageLabel: 'Message',
    messagePlaceholder: undefined,
    submitLabel: 'Send message',
    confidential: false,
});

const fieldId = (name: string): string => `${props.type}-${name}`;
</script>

<template>
    <Form
        v-bind="store.form()"
        reset-on-success
        :options="{ preserveScroll: true }"
        class="space-y-5"
        #default="{ errors, processing, wasSuccessful }"
    >
        <input type="hidden" name="type" :value="type" />

        <div
            class="absolute -left-[10000px] h-px w-px overflow-hidden"
            aria-hidden="true"
        >
            <label :for="fieldId('website')">Leave this field empty</label>
            <input
                :id="fieldId('website')"
                type="text"
                name="website"
                tabindex="-1"
                autocomplete="off"
            />
        </div>

        <div
            v-if="wasSuccessful"
            class="flex gap-3 border-l-4 border-brand-600 bg-brand-50 p-4 text-[0.98rem] text-brand-900"
            role="status"
        >
            <CircleCheck class="mt-0.5 size-5 shrink-0 text-brand-600" />
            <p>
                Thank you. Your message has been received and a member of the
                CHICD team will respond as soon as possible.
            </p>
        </div>

        <div
            v-if="confidential"
            class="flex gap-3 border-l-4 border-sun-400 bg-cream p-4 text-[0.98rem] text-stone-700"
        >
            <Lock class="mt-0.5 size-5 shrink-0 text-brand-700" />
            <p>
                Your message is sent securely and stored in encrypted form. It
                is only seen by the CHICD staff responsible for responding.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label :for="fieldId('name')" class="site-label-text">{{
                    nameLabel
                }}</label>
                <input
                    :id="fieldId('name')"
                    name="name"
                    type="text"
                    required
                    autocomplete="name"
                    class="site-input"
                    :aria-invalid="!!errors.name"
                />
                <p v-if="nameHint" class="mt-1.5 text-sm text-stone-500">
                    {{ nameHint }}
                </p>
                <InputError :message="errors.name" class="mt-1.5" />
            </div>

            <div :class="{ 'sm:col-span-2': !showPhone }">
                <label :for="fieldId('email')" class="site-label-text">
                    Email address
                    <span v-if="emailOrPhone" class="font-normal text-stone-500"
                        >(or phone)</span
                    >
                </label>
                <input
                    :id="fieldId('email')"
                    name="email"
                    type="email"
                    :required="!emailOrPhone"
                    autocomplete="email"
                    class="site-input"
                    :aria-invalid="!!errors.email"
                />
                <InputError :message="errors.email" class="mt-1.5" />
            </div>

            <div v-if="showPhone">
                <label :for="fieldId('phone')" class="site-label-text">
                    Phone number
                    <span
                        v-if="!emailOrPhone"
                        class="font-normal text-stone-500"
                        >(optional)</span
                    >
                </label>
                <input
                    :id="fieldId('phone')"
                    name="phone"
                    type="tel"
                    autocomplete="tel"
                    class="site-input"
                    :aria-invalid="!!errors.phone"
                />
                <InputError :message="errors.phone" class="mt-1.5" />
            </div>

            <div v-if="showOrganisation" class="sm:col-span-2">
                <label :for="fieldId('organisation')" class="site-label-text">
                    Organisation
                    <span class="font-normal text-stone-500">(optional)</span>
                </label>
                <input
                    :id="fieldId('organisation')"
                    name="organisation"
                    type="text"
                    autocomplete="organization"
                    class="site-input"
                />
                <InputError :message="errors.organisation" class="mt-1.5" />
            </div>

            <div v-if="showSubject" class="sm:col-span-2">
                <label :for="fieldId('subject')" class="site-label-text">{{
                    subjectLabel
                }}</label>
                <select
                    v-if="subjectOptions.length"
                    :id="fieldId('subject')"
                    name="subject"
                    class="site-input"
                >
                    <option
                        v-for="option in subjectOptions"
                        :key="option"
                        :value="option"
                        :selected="option === defaultSubject"
                    >
                        {{ option }}
                    </option>
                </select>
                <input
                    v-else
                    :id="fieldId('subject')"
                    name="subject"
                    type="text"
                    :value="defaultSubject"
                    class="site-input"
                />
                <InputError :message="errors.subject" class="mt-1.5" />
            </div>

            <div class="sm:col-span-2">
                <label :for="fieldId('message')" class="site-label-text">{{
                    messageLabel
                }}</label>
                <textarea
                    :id="fieldId('message')"
                    name="message"
                    rows="6"
                    required
                    :placeholder="messagePlaceholder"
                    class="site-input resize-y"
                    :aria-invalid="!!errors.message"
                />
                <InputError :message="errors.message" class="mt-1.5" />
            </div>
        </div>

        <InputError :message="errors.type || errors.website" />

        <SiteButton type="submit" size="lg" :disabled="processing">
            {{ processing ? 'Sending…' : submitLabel }}
        </SiteButton>
    </Form>
</template>
