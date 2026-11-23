<?php

namespace App\Actions\Donations;

use App\Enums\DonationStatus;
use App\Mail\DonationReceipt;
use App\Mail\DonationReceived;
use App\Models\Donation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Records the outcome of a Paystack transaction, from verifying it when the
 * donor returns or from a charge.success webhook. The same payment can be
 * reported both ways; the donor's receipt and CHICD's notification are sent
 * only the first time a donation is confirmed.
 *
 * A charge with a reference CHICD did not create is a later payment of a
 * recurring gift, which Paystack charges itself. It is recorded as a new
 * donation linked to the gift's first payment.
 */
class RecordPaystackCharge
{
    /**
     * @param  array<string, mixed>  $transaction  The transaction's "data" object from Paystack
     */
    public function handle(array $transaction): ?Donation
    {
        $reference = (string) data_get($transaction, 'reference');
        $status = DonationStatus::fromPaystack((string) data_get($transaction, 'status'));

        $donation = Donation::query()->where('reference', $reference)->first()
            ?? ($status === DonationStatus::Successful ? $this->startRenewal($transaction) : null);

        if ($donation === null || $status === null || $donation->isSuccessful()) {
            return $donation;
        }

        if ($status !== DonationStatus::Successful) {
            Donation::query()->whereKey($donation->id)->where('status', '!=', DonationStatus::Successful)->update(['status' => $status]);

            return $donation->refresh();
        }

        if (! $this->matchesDonation($donation, $transaction)) {
            Log::warning('A Paystack charge did not match the donation it was for.', [
                'reference' => $reference,
                'expected' => [$donation->amount, $donation->currency],
                'charged' => [data_get($transaction, 'amount'), data_get($transaction, 'currency')],
            ]);

            return $donation;
        }

        $isFirstConfirmation = $donation->markSuccessful([
            'paystack_transaction_id' => is_numeric(data_get($transaction, 'id')) ? (int) data_get($transaction, 'id') : null,
            'paystack_customer_code' => data_get($transaction, 'customer.customer_code'),
            'channel' => data_get($transaction, 'channel'),
            'fees' => is_numeric(data_get($transaction, 'fees')) ? (int) data_get($transaction, 'fees') : null,
            'paid_at' => $this->paidAt($transaction),
        ]);

        if ($isFirstConfirmation) {
            Mail::to($donation->donor_email, $donation->donor_name)->queue(new DonationReceipt($donation));
            Mail::to(config('chicd.donations_email'))->queue(new DonationReceived($donation));
        }

        return $donation;
    }

    /**
     * Whether Paystack charged the amount and currency the donor chose.
     *
     * @param  array<string, mixed>  $transaction
     */
    private function matchesDonation(Donation $donation, array $transaction): bool
    {
        return (int) data_get($transaction, 'amount') === $donation->amount
            && strtoupper((string) data_get($transaction, 'currency')) === $donation->currency;
    }

    /**
     * Record a later payment of a recurring gift, found by the Paystack
     * customer and plan of the gift's first payment.
     *
     * @param  array<string, mixed>  $transaction
     */
    private function startRenewal(array $transaction): ?Donation
    {
        $customerCode = data_get($transaction, 'customer.customer_code');
        $planCode = self::planCode($transaction);

        if (! is_string($customerCode) || $planCode === null) {
            return null;
        }

        $firstPayment = Donation::query()
            ->successful()
            ->whereNull('parent_id')
            ->where('paystack_customer_code', $customerCode)
            ->where('paystack_plan_code', $planCode)
            ->latest('id')
            ->first();

        if ($firstPayment === null) {
            Log::info('Ignored a Paystack charge that is not for a known donation.', ['reference' => data_get($transaction, 'reference')]);

            return null;
        }

        return Donation::query()->createOrFirst(['reference' => (string) data_get($transaction, 'reference')], [
            'parent_id' => $firstPayment->id,
            ...$firstPayment->only(['fund', 'fund_label', 'frequency', 'amount', 'currency', 'donor_name', 'donor_email', 'donor_phone', 'paystack_customer_code', 'paystack_plan_code']),
        ]);
    }

    /**
     * The plan code of a subscription charge. Paystack sends the plan as an
     * object in webhooks and may send it as a code, or as plan_object, when a
     * transaction is verified.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function planCode(array $payload): ?string
    {
        $plan = data_get($payload, 'plan');
        $code = is_array($plan) ? ($plan['plan_code'] ?? null) : $plan;
        $code ??= data_get($payload, 'plan_object.plan_code');

        return is_string($code) && $code !== '' ? $code : null;
    }

    /**
     * When Paystack says the payment was made, or now if it does not say.
     *
     * @param  array<string, mixed>  $transaction
     */
    private function paidAt(array $transaction): CarbonImmutable
    {
        $paidAt = data_get($transaction, 'paid_at') ?? data_get($transaction, 'paidAt');

        return is_string($paidAt) && $paidAt !== '' ? CarbonImmutable::parse($paidAt)->utc() : now()->utc();
    }
}
