<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Donations\ExportDonations;
use App\Concerns\ResolvesRequestedMonth;
use App\Enums\DonationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminDonationResource;
use App\Models\Donation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DonationController extends Controller
{
    use ResolvesRequestedMonth;

    /**
     * The donations paid in a month, with totals for each fund, and the
     * donations started that month that were never paid.
     */
    public function index(Request $request): Response
    {
        $month = $this->requestedMonth($request->query('month'));
        $donations = $this->paidDonations($month);
        $currency = config('services.paystack.currency');

        return Inertia::render('admin/donations/Index', [
            ...$this->monthNavigation($month),
            'totals' => [
                'count' => $donations->count(),
                'amount' => Donation::formatMoney((int) $donations->sum('amount'), $currency),
                'fees' => Donation::formatMoney((int) $donations->sum('fees'), $currency),
                'net' => Donation::formatMoney((int) $donations->sum('amount') - (int) $donations->sum('fees'), $currency),
            ],
            'funds' => $donations->groupBy('fund_label')
                ->map(fn (Collection $group, string $label): array => [
                    'fund' => $label,
                    'count' => $group->count(),
                    'amount' => Donation::formatMoney((int) $group->sum('amount'), $currency),
                ])
                ->sortByDesc('count')
                ->values(),
            'donations' => AdminDonationResource::collection($donations)->resolve(),
            'incomplete' => AdminDonationResource::collection(
                Donation::query()
                    ->whereNot('status', DonationStatus::Successful)
                    ->whereBetween('created_at', [$month->utc(), $month->endOfMonth()->utc()])
                    ->latest()
                    ->latest('id')
                    ->limit(50)
                    ->get()
            )->resolve(),
        ]);
    }

    /**
     * Download the donations paid in a month as a CSV file.
     */
    public function export(ExportDonations $export, string $month): StreamedResponse
    {
        $month = $this->monthFromPath($month);

        return $export->handle(
            $this->paidDonations($month)->sortBy('paid_at')->values(),
            'chicd-donations-'.$month->format('Y-m'),
        );
    }

    /**
     * Confirmed donations paid in the month, newest first.
     *
     * @return Collection<int, Donation>
     */
    private function paidDonations(CarbonImmutable $month): Collection
    {
        return Donation::query()
            ->successful()
            ->paidInMonth($month)
            ->with('parent:id,reference,paystack_subscription_code')
            ->latest('paid_at')
            ->latest('id')
            ->get();
    }
}
