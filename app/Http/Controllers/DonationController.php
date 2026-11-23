<?php

namespace App\Http\Controllers;

use App\Actions\Donations\ListDonationFunds;
use App\Actions\Donations\RecordPaystackCharge;
use App\Actions\Donations\StartDonation;
use App\Enums\DonationFrequency;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class DonationController extends Controller
{
    /**
     * Show the Donate page, with the fund named in ?fund= chosen (such as
     * ?fund=surl from a programme page).
     */
    public function create(Request $request, ListDonationFunds $listFunds): Response
    {
        $funds = $listFunds->handle();
        $requestedFund = $request->query('fund');

        return Inertia::render('site/Donate', [
            'funds' => collect($funds)->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])->values(),
            'frequencies' => collect(DonationFrequency::cases())->map(fn (DonationFrequency $frequency): array => ['value' => $frequency->value, 'label' => $frequency->label()]),
            'selectedFund' => is_string($requestedFund) && array_key_exists($requestedFund, $funds) ? $requestedFund : 'general',
            'amounts' => config('chicd.donation_amounts'),
        ]);
    }

    /**
     * Start a donation. The Donate page receives the transaction as flash
     * data and opens the Paystack popup for it, or sends the donor to
     * Paystack's checkout page if the popup cannot load.
     */
    public function store(StoreDonationRequest $request, StartDonation $startDonation): RedirectResponse
    {
        try {
            $payment = $startDonation->handle($request->donationDetails());
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'payment' => 'We could not reach our payment provider. Your card has not been charged. Please try again in a few minutes.',
            ]);
        }

        Inertia::flash('paystack', $payment);

        return back();
    }

    /**
     * Where Paystack sends the donor after paying. The payment is checked
     * with Paystack here as well as by webhook, so the donor sees the result
     * straight away.
     */
    public function callback(Request $request, RecordPaystackCharge $recordCharge): Response
    {
        $reference = $request->string('reference')->toString() ?: $request->string('trxref')->toString();
        $donation = Donation::query()->where('reference', $reference)->firstOrFail();

        if (! $donation->isSuccessful()) {
            try {
                $transaction = Http::paystack()
                    ->retry(2, 500, fn (Throwable $exception): bool => $exception instanceof ConnectionException || ($exception instanceof RequestException && $exception->response->serverError()), throw: false)
                    ->get('transaction/verify/'.rawurlencode($donation->reference))
                    ->throw()
                    ->json('data');

                if (is_array($transaction)) {
                    $donation = $recordCharge->handle($transaction) ?? $donation;
                }
            } catch (ConnectionException|RequestException $exception) {
                report($exception);
            }
        }

        return Inertia::render('site/DonationStatus', [
            'donation' => [
                'reference' => $donation->reference,
                'status' => $donation->status->value,
                'firstName' => strtok($donation->donor_name, ' ') ?: $donation->donor_name,
                'amount' => $donation->formattedAmount(),
                'fund' => $donation->fund_label,
                'frequency' => $donation->frequency->value,
                'frequencyLabel' => $donation->frequency->label(),
            ],
        ]);
    }
}
