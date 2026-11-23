/**
 * Paystack's popup checkout (Paystack Inline v2), loaded from Paystack on
 * demand rather than bundled, so card details stay inside Paystack's own
 * iframe.
 *
 * The server initializes each transaction and sends only its access code,
 * so the amount, plan and reference cannot be changed in the browser.
 */

const SCRIPT_URL = 'https://js.paystack.co/v2/inline.js';

/** A transaction started on the server, as flashed by DonationController. */
export type PaystackPayment = {
    reference: string;
    accessCode: string;
    /** Paystack's hosted checkout page, used if the popup cannot open. */
    checkoutUrl: string;
};

type PaystackCallbacks = {
    onSuccess?: (transaction: {
        id: number;
        reference: string;
        message: string;
    }) => void;
    onCancel?: () => void;
    onError?: (error: { message: string }) => void;
};

type PaystackPopInstance = {
    resumeTransaction: (
        accessCode: string,
        callbacks?: PaystackCallbacks,
    ) => unknown;
};

type PaystackPopConstructor = new () => PaystackPopInstance;

declare global {
    interface Window {
        PaystackPop?: PaystackPopConstructor;
    }
}

let loading: Promise<PaystackPopConstructor> | null = null;

/**
 * Load Paystack's popup script once. Rejects if it cannot load, for example
 * when a browser extension blocks it.
 */
export function loadPaystack(): Promise<PaystackPopConstructor> {
    if (window.PaystackPop) {
        return Promise.resolve(window.PaystackPop);
    }

    loading ??= new Promise<PaystackPopConstructor>((resolve, reject) => {
        const script = document.createElement('script');
        script.src = SCRIPT_URL;
        script.async = true;
        script.onload = () =>
            window.PaystackPop
                ? resolve(window.PaystackPop)
                : reject(new Error('Paystack did not load.'));
        script.onerror = () => {
            loading = null;
            script.remove();
            reject(new Error('Paystack could not be loaded.'));
        };
        document.head.appendChild(script);
    });

    return loading;
}

/**
 * Open the Paystack popup for a transaction started on the server. If the
 * popup cannot load, the donor is sent to Paystack's checkout page instead,
 * which returns them to the thank-you page.
 */
export async function openPaystackCheckout(
    payment: PaystackPayment,
    callbacks: { onSuccess: () => void; onCancel: () => void },
): Promise<void> {
    const sendToCheckoutPage = (): void => {
        window.location.assign(payment.checkoutUrl);
    };

    try {
        const PaystackPop = await loadPaystack();

        new PaystackPop().resumeTransaction(payment.accessCode, {
            onSuccess: callbacks.onSuccess,
            onCancel: callbacks.onCancel,
            onError: sendToCheckoutPage,
        });
    } catch {
        sendToCheckoutPage();
    }
}
