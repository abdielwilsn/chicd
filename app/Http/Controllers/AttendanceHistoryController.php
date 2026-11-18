<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldActivityResource;
use App\Http\Resources\OfficeAttendanceResource;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceHistoryController extends Controller
{
    /**
     * Show the staff member's own office sessions and field activities for
     * one month, grouped by day.
     *
     * Office time and field time are totalled separately: how overlapping
     * office and field time count towards hours is a timesheet rule CHICD
     * has not set yet.
     */
    public function index(Request $request): Response
    {
        $timezone = config('chicd.timezone');
        $month = $this->requestedMonth($request->string('month')->toString(), $timezone);
        $nextMonth = $month->addMonth();
        $user = $request->user();

        $officeAttendances = $user->officeAttendances()
            ->startedBetween($month, $nextMonth)
            ->with('office')
            ->oldest('clocked_in_at')
            ->oldest('id')
            ->get();

        $fieldActivities = $user->fieldActivities()
            ->startedBetween($month, $nextMonth)
            ->oldest('started_at')
            ->oldest('id')
            ->get();

        $officeByDay = $officeAttendances->groupBy(fn (OfficeAttendance $attendance) => $attendance->clocked_in_at->setTimezone($timezone)->toDateString());
        $fieldByDay = $fieldActivities->groupBy(fn (FieldActivity $activity) => $activity->started_at->setTimezone($timezone)->toDateString());

        $days = $officeByDay->keys()
            ->merge($fieldByDay->keys())
            ->unique()
            ->sortDesc()
            ->map(fn (string $date) => [
                'date' => $date,
                'label' => CarbonImmutable::parse($date, $timezone)->format('l j F'),
                'officeAttendances' => OfficeAttendanceResource::collection($officeByDay->get($date, collect()))->resolve(),
                'fieldActivities' => FieldActivityResource::collection($fieldByDay->get($date, collect()))->resolve(),
            ])
            ->values()
            ->all();

        $currentMonth = now($timezone)->startOfMonth();

        return Inertia::render('attendance/Index', [
            'month' => $month->format('Y-m'),
            'monthLabel' => $month->format('F Y'),
            'previousMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->lessThan($currentMonth) ? $nextMonth->format('Y-m') : null,
            'days' => $days,
            'totals' => [
                'officeMinutes' => $this->closedMinutes($officeAttendances, 'clocked_in_at', 'clocked_out_at'),
                'fieldMinutes' => $this->closedMinutes($fieldActivities, 'started_at', 'ended_at'),
                'officeDays' => $officeByDay->count(),
                'fieldDays' => $fieldByDay->count(),
            ],
        ]);
    }

    /**
     * The first moment of the requested month (YYYY-MM) in the organisation
     * timezone, falling back to the current month for a missing, malformed
     * or future month.
     */
    private function requestedMonth(string $requested, string $timezone): CarbonImmutable
    {
        $currentMonth = now($timezone)->startOfMonth();

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requested) !== 1) {
            return $currentMonth;
        }

        $month = CarbonImmutable::createFromFormat('!Y-m', $requested, $timezone);

        return $month->greaterThan($currentMonth) ? $currentMonth : $month;
    }

    /**
     * Total minutes across records that have both a start and an end.
     *
     * @param  iterable<OfficeAttendance|FieldActivity>  $records
     */
    private function closedMinutes(iterable $records, string $startColumn, string $endColumn): int
    {
        $minutes = 0;

        foreach ($records as $record) {
            if ($record->{$endColumn} !== null) {
                $minutes += (int) $record->{$startColumn}->diffInMinutes($record->{$endColumn});
            }
        }

        return $minutes;
    }
}
