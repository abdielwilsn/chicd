/**
 * Machine translation of the public website with Google's website
 * translator.
 *
 * The visitor's choice lives in Google's own `googtrans` cookie, which the
 * translator reads when it starts, so a choice carries over to every page
 * and later visits. Google's script is only loaded once someone picks a
 * language other than English: everyone else never contacts Google.
 *
 * Only the public website is translated. The staff portal stays in English,
 * where a mistranslated leave reason or payroll note could mislead.
 */

import { router } from '@inertiajs/vue3';

export type TranslationLanguage = {
    code: string;
    name: string;
};

const pageLanguage = 'en';

/** Languages offered, each named in its own language. */
export const translationLanguages: TranslationLanguage[] = [
    { code: 'en', name: 'English' },
    { code: 'ha', name: 'Hausa' },
    { code: 'yo', name: 'Yorùbá' },
    { code: 'ig', name: 'Igbo' },
    { code: 'fr', name: 'Français' },
    { code: 'ar', name: 'العربية' },
    { code: 'pt', name: 'Português' },
    { code: 'es', name: 'Español' },
    { code: 'de', name: 'Deutsch' },
    { code: 'zh-CN', name: '中文' },
];

const cookieName = 'googtrans';
const scriptUrl =
    'https://translate.google.com/translate_a/element.js?cb=chicdStartGoogleTranslate';

/** The language the site is shown in, from the `googtrans` cookie. */
export function currentTranslationLanguage(): string {
    const match = document.cookie.match(/(?:^|;\s*)googtrans=([^;]*)/);
    const target = match
        ? decodeURIComponent(match[1]).split('/').pop()
        : undefined;

    return translationLanguages.some((language) => language.code === target)
        ? (target as string)
        : pageLanguage;
}

/** Switch the site to a language and reload so every page follows it. */
export function translateSiteTo(code: string): void {
    clearTranslationCookie();

    if (code !== pageLanguage) {
        document.cookie = `${cookieName}=/${pageLanguage}/${code}; path=/; max-age=31536000; SameSite=Lax`;
    }

    window.location.reload();
}

/**
 * Load Google's translator when a language has been chosen. Safe to call
 * more than once.
 */
export function startGoogleTranslate(): void {
    if (
        currentTranslationLanguage() === pageLanguage ||
        document.getElementById('google-translate-script')
    ) {
        return;
    }

    toleratePageTranslation();
    leaveTranslationOutsideTheWebsite();

    const container = document.createElement('div');
    container.id = 'google-translate-element';
    container.hidden = true;
    document.body.append(container);

    const callbacks = window as unknown as Record<string, unknown>;

    callbacks.chicdStartGoogleTranslate = (): void => {
        const google = (
            window as unknown as {
                google?: {
                    translate?: {
                        TranslateElement: new (
                            options: Record<string, unknown>,
                            elementId: string,
                        ) => unknown;
                    };
                };
            }
        ).google;

        if (google?.translate) {
            new google.translate.TranslateElement(
                {
                    pageLanguage,
                    includedLanguages: translationLanguages
                        .map((language) => language.code)
                        .join(','),
                    autoDisplay: false,
                },
                container.id,
            );
        }
    };

    const script = document.createElement('script');
    script.id = 'google-translate-script';
    script.src = scriptUrl;
    script.async = true;
    document.head.append(script);
}

/**
 * Google sets the cookie itself too, on the host and on the parent domain,
 * so it has to be removed from each of them.
 */
function clearTranslationCookie(): void {
    const expired = `${cookieName}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
    const parts = window.location.hostname.split('.');

    document.cookie = expired;

    for (let index = 0; index < parts.length - 1; index++) {
        const domain = parts.slice(index).join('.');

        document.cookie = `${expired}; domain=${domain}`;
        document.cookie = `${expired}; domain=.${domain}`;
    }
}

/**
 * Once loaded, the translator would carry on translating pages reached
 * without a full page load, so going from the website to the staff portal
 * loads the portal afresh, without it.
 */
function leaveTranslationOutsideTheWebsite(): void {
    router.on('navigate', (event) => {
        if (!event.detail.page.component.startsWith('site/')) {
            window.location.reload();
        }
    });
}

let isTolerant = false;

/**
 * The translator swaps text for translated copies behind Vue's back. When
 * Vue later inserts next to a text node that is no longer in the page, the
 * browser throws and the page stops updating. Insert next to what took its
 * place instead.
 */
function toleratePageTranslation(): void {
    if (isTolerant) {
        return;
    }

    isTolerant = true;

    const insertBefore = Node.prototype.insertBefore;

    Node.prototype.insertBefore = function <T extends Node>(
        this: Node,
        node: T,
        reference: Node | null,
    ): T {
        if (reference && reference.parentNode !== this) {
            let ancestor: Node | null = reference;

            while (ancestor && ancestor.parentNode !== this) {
                ancestor = ancestor.parentNode;
            }

            return insertBefore.call(this, node, ancestor) as T;
        }

        return insertBefore.call(this, node, reference) as T;
    };

    const removeChild = Node.prototype.removeChild;

    Node.prototype.removeChild = function <T extends Node>(
        this: Node,
        child: T,
    ): T {
        if (child.parentNode !== this) {
            return child;
        }

        return removeChild.call(this, child) as T;
    };
}
