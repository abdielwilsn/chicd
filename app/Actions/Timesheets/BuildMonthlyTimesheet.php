<?php

namespace App\Actions\Timesheets;

use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Works out a staff member's monthly timesheet from the office sessions and
 * field activities they recorded. Nothing is entered by hand.
 *
 * Office time and field time are totalled separately: how overlapping office
 * and field time count towards hours is a rule CHICD has not set yet. Open
 * records are listed but not counted.
 *
 * @phpstan-type TimesheetDay array{date: string, label: string, officeAttendances: Collection<int, OfficeAttendance>, fieldActivities: Collection<int, FieldActivity>, officeMinutes: int, fieldMinutes: int}
 * @phpstan-type TimesheetTotals array{officeMinutes: int, fieldMinutes: int, officeDays: int, fieldDays: int, daysWorked: int, flaggedClockings: int, openRecords: int}
 * @phpstan-type MonthlyTimesheet array{month: CarbonImmutable, days: list<TimesheetDay>, totals: TimesheetTotals}
 */
class BuildMonthlyTimesheet
{
    /**
     * Build the timesheet for one staff member and month, loading their records.
     *
     * @param  CarbonImmutable  $month  The first moment of the month, in organisation time.
     * @return MonthlyTimesheet
     */
    public function handle(User $user, CarbonImmutable $month): array
    {
        $nextMonth = $month->addMonth();

        return $this->fromRecords(
            $month,
            $user->officeAttendances()->startedBetween($month, $nextMonth)->with('office')->oldest('clocked_in_at')->oldest('id')->get(),
            $user->fieldActivities()->startedBetween($month, $nextMonth)->oldest('started_at')->oldest('id')->get(),
        );
    }

    /**
     * Build a timesheet from records already loaded for one staff member and
     * month, oldest first.
     *
     * @param  Collection<int, OfficeAttendance>  $officeAttendances
     * @param  Collection<int, FieldActivity>  $fieldActivities
     * @return MonthlyTimesheet
     */
    public function fromRecords(CarbonImmutable $month, Collection $officeAttendances, Collection $fieldActivities): array
    {
        $timezone = config('chicd.timezone');

        $officeByDay = [];
        foreach ($officeAttendances as $attendance) {
            $officeByDay[$attendance->clocked_in_at->setTimezone($timezone)->toDateString()][] = $attendance;
        }

        $fieldByDay = [];
        foreach ($fieldActivities as $activity) {
            $fieldByDay[$activity->started_at->setTimezone($timezone)->toDateString()][] = $activity;
        }

        $dates = array_unique([...array_keys($officeByDay), ...array_keys($fieldByDay)]);
        sort($dates);

        $days = [];
        foreach ($dates as $date) {
            $office = collect($officeByDay[$date] ?? []);
            $field = collect($fieldByDay[$date] ?? []);

            $days[] = [
                'date' => $date,
                'label' => CarbonImmutable::parse($date, $timezone)->format('l j F'),
                'officeAttendances' => $office,
                'fieldActivities' => $field,
                'officeMinutes' => $office->sum(fn (OfficeAttendance $attendance): int => self::officeMinutes($attendance) ?? 0),
                'fieldMinutes' => $field->sum(fn (FieldActivity $activity): int => self::fieldMinutes($activity) ?? 0),
            ];
        }

        return [
            'month' => $month,
            'days' => $days,
            'totals' => [
                'officeMinutes' => array_sum(array_column($days, 'officeMinutes')),
                'fieldMinutes' => array_sum(array_column($days, 'fieldMinutes')),
                'officeDays' => count($officeByDay),
                'fieldDays' => count($fieldByDay),
                'daysWorked' => count($days),
                'flaggedClockings' => $officeAttendances->filter(fn (OfficeAttendance $attendance): bool => self::isFlagged($attendance))->count(),
                'openRecords' => $officeAttendances->filter->isOpen()->count() + $fieldActivities->filter->isOpen()->count(),
            ],
        ];
    }

    /**
     * Minutes in a clocked-out office session, or null while it is open.
     */
    public static function officeMinutes(OfficeAttendance $attendance): ?int
    {
        return $attendance->clocked_out_at ? (int) $attendance->clocked_in_at->diffInMinutes($attendance->clocked_out_at) : null;
    }

    /**
     * Minutes in an ended field activity, or null while it is running.
     */
    public static function fieldMinutes(FieldActivity $activity): ?int
    {
        return $activity->ended_at ? (int) $activity->started_at->diffInMinutes($activity->ended_at) : null;
    }

    /**
     * Whether either end of an office session fell outside the geofence.
     */
    public static function isFlagged(OfficeAttendance $attendance): bool
    {
        return ! $attendance->clocked_in_within_geofence || $attendance->clocked_out_within_geofence === false;
    }
}
