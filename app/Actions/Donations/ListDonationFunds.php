<?php

namespace App\Actions\Donations;

use App\Models\Programme;

/**
 * The funds a donor can give to: CHICD generally and the flagship
 * programmes (from config/chicd.php), then every published programme added
 * by an admin, in website order.
 */
class ListDonationFunds
{
    /**
     * @return array<string, string> Labels keyed by fund (a programme's slug)
     */
    public function handle(): array
    {
        $funds = config('chicd.donation_funds');

        foreach (Programme::query()->published()->ordered()->get(['name', 'slug']) as $programme) {
            $funds[$programme->slug] ??= $programme->name;
        }

        return $funds;
    }
}
