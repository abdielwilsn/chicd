<?php

namespace App\Actions\Donations;

use App\Models\Donation;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads donations as a CSV file for financial reconciliation, one row
 * per payment. Amounts are in naira with two decimal places, so they can be
 * summed in Excel and matched against Paystack settlements.
 */
class ExportDonations
{
    /**
     * @param  Collection<int, Donation>  $donations  With the parent relation loaded
     */
    public function handle(Collection $donations, string $filename): StreamedResponse
    {
        $timezone = config('chicd.timezone');

        return response()->streamDownload(function () use ($donations, $timezone): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new RuntimeException('Could not open the output stream for the donations CSV.');
            }

            // A byte-order mark so Excel opens the file as UTF-8.
            fwrite($output, "\u{FEFF}");
            fputcsv($output, [
                'Reference', 'Date paid', 'Donor', 'Email', 'Phone', 'Supporting', 'Fund code', 'Frequency',
                'Repeat payment of', 'Currency', 'Amount', 'Paystack fees', 'Net amount', 'Paid by', 'Status',
                'Paystack transaction ID', 'Paystack subscription',
            ], escape: '');

            foreach ($donations as $donation) {
                fputcsv($output, [
                    $donation->reference,
                    $donation->paid_at?->setTimezone($timezone)->format('Y-m-d H:i'),
                    $donation->donor_name,
                    $donation->donor_email,
                    $donation->donor_phone,
                    $donation->fund_label,
                    $donation->fund,
                    $donation->frequency->label(),
                    $donation->parent?->reference,
                    $donation->currency,
                    $this->amount($donation->amount),
                    $donation->fees === null ? null : $this->amount($donation->fees),
                    $donation->fees === null ? null : $this->amount($donation->amount - $donation->fees),
                    $donation->channel,
                    $donation->status->label(),
                    $donation->paystack_transaction_id,
                    $donation->paystack_subscription_code ?? $donation->parent?->paystack_subscription_code,
                ], escape: '');
            }

            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * An amount in the smallest currency unit as a plain decimal, such as
     * "50000.00".
     */
    private function amount(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
