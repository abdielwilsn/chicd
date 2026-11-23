<?php

namespace App\Concerns;

use Carbon\CarbonImmutable;

/**
 * Turns a "YYYY-MM" month from the query string or URL into the first moment
 * of that month in the organisation timezone.
 */
trait ResolvesRequestedMonth
{
    /**
     * The requested month, falling back to the current month for a missing,
     * malformed or future month.
     */
    protected function requestedMonth(?string $requested): CarbonImmutable
    {
        $timezone = config('chicd.timezone');
        $currentMonth = now($timezone)->startOfMonth();

        if ($requested === null || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requested) !== 1) {
            return $currentMonth;
        }

        $month = CarbonImmutable::createFromFormat('!Y-m', $requested, $timezone);

        return $month->greaterThan($currentMonth) ? $currentMonth : $month;
    }

    /**
     * A month named in the URL. Unlike the query string, a malformed or
     * future month here is not found rather than replaced.
     */
    protected function monthFromPath(string $month): CarbonImmutable
    {
        $resolved = $this->requestedMonth($month);

        abort_unless($resolved->format('Y-m') === $month, 404);

        return $resolved;
    }

    /**
     * Month navigation props shared by the pages that show one month.
     *
     * @return array{month: string, monthLabel: string, previousMonth: string, nextMonth: string|null}
     */
    protected function monthNavigation(CarbonImmutable $month): array
    {
        $currentMonth = now(config('chicd.timezone'))->startOfMonth();

        return [
            'month' => $month->format('Y-m'),
            'monthLabel' => $month->format('F Y'),
            'previousMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->lessThan($currentMonth) ? $month->addMonth()->format('Y-m') : null,
        ];
    }
}
