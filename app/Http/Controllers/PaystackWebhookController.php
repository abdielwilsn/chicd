<?php

namespace App\Http\Controllers;

use App\Actions\Donations\RecordPaystackCharge;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Receives Paystack's webhook events. Paystack signs each event with an
 * HMAC-SHA512 of the body, keyed with the secret key, in the
 * X-Paystack-Signature header; unsigned or wrongly signed events are refused.
 *
 * Set the webhook URL in the Paystack dashboard to route('webhooks.paystack').
 */
class PaystackWebhookController extends Controller
{
    /**
     * Handle a Paystack event. Events that are not about donations are
     * acknowledged and ignored, so Paystack does not keep resending them.
     */
    public function __invoke(Request $request, RecordPaystackCharge $recordCharge): Response
    {
        $secret = (string) config('services.paystack.secret_key');
        $signature = (string) $request->header('X-Paystack-Signature');

        abort_if($secret === '' || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature), 401);

        $data = $request->json('data');

        if (is_array($data)) {
            match ($request->json('event')) {
                'charge.success' => $recordCharge->handle($data),
                'subscription.create' => $this->recordSubscription($data),
                default => null,
            };
        }

        return response()->noContent(200);
    }

    /**
     * Keep the subscription code on the first payment of a recurring gift,
     * so the subscription can be found in the Paystack dashboard. Paystack
     * may send this before the first payment is confirmed, when the donation
     * has no customer code yet, so the donor's email address is used then.
     *
     * @param  array<string, mixed>  $subscription
     */
    private function recordSubscription(array $subscription): void
    {
        $code = data_get($subscription, 'subscription_code');
        $customerCode = data_get($subscription, 'customer.customer_code');
        $planCode = RecordPaystackCharge::planCode($subscription);

        if (! is_string($code) || ! is_string($customerCode) || $planCode === null) {
            return;
        }

        Donation::query()
            ->whereNull('parent_id')
            ->whereNull('paystack_subscription_code')
            ->where('paystack_plan_code', $planCode)
            ->where(fn (Builder $query) => $query
                ->where('paystack_customer_code', $customerCode)
                ->orWhere(fn (Builder $query) => $query->whereNull('paystack_customer_code')->where('donor_email', data_get($subscription, 'customer.email'))))
            ->latest('id')
            ->first()
            ?->forceFill(['paystack_subscription_code' => $code, 'paystack_customer_code' => $customerCode])
            ->save();
    }
}
