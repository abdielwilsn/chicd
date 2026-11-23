import type { InertiaForm } from '@inertiajs/vue3';

/**
 * Submit an admin form that may carry files. Files only travel in a POST, so
 * an update is sent as a spoofed PUT.
 */
export function submitAdminForm<T extends object>(
    form: InertiaForm<T>,
    storeUrl: string,
    updateUrl: string | null,
): void {
    if (updateUrl) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(updateUrl);

        return;
    }

    form.post(storeUrl);
}
