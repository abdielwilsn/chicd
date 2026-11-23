<?php

namespace App\Actions\Donations;

use App\Enums\DonationFrequency;
use App\Models\Donation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Starts a donation: initializes a Paystack transaction for the gift and
 * records the donation as pending until Paystack confirms the payment.
 *
 * The amount, plan and reference are set here on the server. The donor's
 * browser only receives the transaction's access code, which opens the
 * Paystack popup for exactly this transaction, and the hosted checkout
 * address as a fallback.
 *
 * A recurring gift is a Paystack subscription. Paystack charges the plan's
 * amount, so each recurring amount and interval gets its own plan, created
 * the first time a donor chooses it.
 */
class StartDonation
{
    public function __construct(private ListDonationFunds $listFunds) {}

    /**
     * @param  array{fund: string, frequency: string, amount: int, name: string, email: string, phone?: string|null}  $details  Amount in whole naira
     * @return array{reference: string, accessCode: string, checkoutUrl: string}
     *
     * @throws ConnectionException|RequestException When Paystack cannot be reached or refuses the request
     */
    public function handle(array $details): array
    {
        $frequency = DonationFrequency::from($details['frequency']);
        $currency = config('services.paystack.currency');
        $amount = $details['amount'] * 100;
        $fundLabel = $this->listFunds->handle()[$details['fund']];
        $reference = 'CHICD-'.Str::ulid();
        $planCode = $frequency->isRecurring() ? $this->planCode($frequency, $amount, $currency) : null;

        $transaction = Http::paystack()->post('transaction/initialize', array_filter([
            'email' => $details['email'],
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'callback_url' => route('donations.callback'),
            'plan' => $planCode,
            'metadata' => [
                'fund' => $details['fund'],
                'custom_fields' => [
                    ['display_name' => 'Donor', 'variable_name' => 'donor_name', 'value' => $details['name']],
                    ['display_name' => 'Supporting', 'variable_name' => 'fund', 'value' => $fundLabel],
                    ['display_name' => 'Frequency', 'variable_name' => 'frequency', 'value' => $frequency->label()],
                ],
            ],
        ]))->throw()->json('data');

        $accessCode = $transaction['access_code'] ?? null;
        $checkoutUrl = $transaction['authorization_url'] ?? null;

        if (! is_string($accessCode) || $accessCode === '' || ! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new RuntimeException('Paystack did not return an access code and checkout address for donation '.$reference.'.');
        }

        Donation::create([
            'reference' => $reference,
            'fund' => $details['fund'],
            'fund_label' => $fundLabel,
            'frequency' => $frequency,
            'amount' => $amount,
            'currency' => $currency,
            'donor_name' => $details['name'],
            'donor_email' => $details['email'],
            'donor_phone' => $details['phone'] ?? null,
            'paystack_plan_code' => $planCode,
        ]);

        return ['reference' => $reference, 'accessCode' => $accessCode, 'checkoutUrl' => $checkoutUrl];
    }

    /**
     * The Paystack plan for a recurring amount, created on first use.
     *
     * @throws ConnectionException|RequestException
     */
    private function planCode(DonationFrequency $frequency, int $amount, string $currency): string
    {
        return Cache::rememberForever(
            sprintf('paystack.donation-plan.%s.%s.%d', $frequency->value, $currency, $amount),
            fn (): string => Http::paystack()->post('plan', [
                'name' => sprintf('%s %s gift of %s', config('chicd.short_name'), Str::lower($frequency->label()), Donation::formatMoney($amount, $currency)),
                'interval' => $frequency->paystackInterval(),
                'amount' => $amount,
                'currency' => $currency,
            ])->throw()->json('data.plan_code') ?? throw new RuntimeException('Paystack did not return a plan code.'),
        );
    }
}
