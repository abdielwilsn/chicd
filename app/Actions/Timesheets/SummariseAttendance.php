<?php

namespace App\Actions\Timesheets;

use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Works out lateness, absence and leave for a staff member's month, from
 * their timesheet, their approved leave and the public holidays.
 *
 * The rules (config/chicd.php, working_hours):
 * - Working days are the configured weekdays, except public holidays, from
 *   the day the account was created until the day it was deactivated.
 * - A working day covered by approved leave is a leave day, whatever else
 *   was recorded on it.
 * - Starting work (the first clock-in or field activity) after the start
 *   time plus the grace period is late.
 * - A past working day with no record and no leave is an absence. Today only
 *   counts once it is over.
 *
 * @phpstan-import-type MonthlyTimesheet from BuildMonthlyTimesheet
 *
 * @phpstan-type AttendanceDay array{date: string, label: string}
 * @phpstan-type LateDay array{date: string, label: string, startedAt: string}
 * @phpstan-type LeaveDay array{date: string, label: string, type: string}
 * @phpstan-type HolidayDay array{date: string, label: string, name: string}
 * @phpstan-type AttendanceSummary array{workingDays: int, lateDays: list<LateDay>, absentDays: list<AttendanceDay>, leaveDays: list<LeaveDay>, holidays: list<HolidayDay>}
 */
class SummariseAttendance
{
    /**
     * Summarise one staff member's month, loading their leave and the
     * holidays.
     *
     * @param  MonthlyTimesheet  $timesheet
     * @return AttendanceSummary
     */
    public function handle(User $user, CarbonImmutable $month, array $timesheet): array
    {
        return $this->fromRecords(
            $user,
            $month,
            $timesheet,
            $user->leaveRequests()->approved()->overlapping($month, $month->endOfMonth())->get(),
            self::holidaysIn($month),
        );
    }

    /**
     * Summarise a month from leave and holidays already loaded.
     *
     * @param  MonthlyTimesheet  $timesheet
     * @param  Collection<int, LeaveRequest>  $approvedLeave  The staff member's approved leave
     * @param  Collection<string, PublicHoliday>  $holidays  Keyed by date (Y-m-d)
     * @return AttendanceSummary
     */
    public function fromRecords(User $user, CarbonImmutable $month, array $timesheet, Collection $approvedLeave, Collection $holidays): array
    {
        $timezone = config('chicd.timezone');
        $workingWeekdays = config('chicd.working_hours.days');
        [$startHour, $startMinute] = array_map(intval(...), explode(':', config('chicd.working_hours.start')));
        $graceMinutes = (int) config('chicd.working_hours.grace_minutes');
        $today = now($timezone)->toDateString();

        $firstDay = max($month->toDateString(), $user->created_at?->setTimezone($timezone)->toDateString() ?? $month->toDateString());
        $lastDay = min($month->endOfMonth()->toDateString(), $user->deactivated_at?->setTimezone($timezone)->toDateString() ?? $month->endOfMonth()->toDateString());

        $startedAtByDate = $this->firstStartByDate($timesheet);
        $leaveByDate = $this->leaveTypeByDate($approvedLeave);

        $summary = ['workingDays' => 0, 'lateDays' => [], 'absentDays' => [], 'leaveDays' => [], 'holidays' => []];

        for ($day = $month; $day->lessThanOrEqualTo($month->endOfMonth()); $day = $day->addDay()) {
            $date = $day->toDateString();

            if ($date < $firstDay || $date > $lastDay || ! in_array($day->dayOfWeekIso, $workingWeekdays, true)) {
                continue;
            }

            $label = $day->format('l j F');

            if ($holidays->has($date)) {
                $summary['holidays'][] = ['date' => $date, 'label' => $label, 'name' => $holidays->get($date)->name];

                continue;
            }

            $summary['workingDays']++;

            if (isset($leaveByDate[$date])) {
                $summary['leaveDays'][] = ['date' => $date, 'label' => $label, 'type' => $leaveByDate[$date]];
            } elseif (isset($startedAtByDate[$date])) {
                if ($startedAtByDate[$date]->greaterThan($day->setTime($startHour, $startMinute)->addMinutes($graceMinutes))) {
                    $summary['lateDays'][] = ['date' => $date, 'label' => $label, 'startedAt' => $startedAtByDate[$date]->format('H:i')];
                }
            } elseif ($date < $today) {
                $summary['absentDays'][] = ['date' => $date, 'label' => $label];
            }
        }

        return $summary;
    }

    /**
     * The lateness rule in words, such as "08:00 (with 15 minutes' grace)".
     */
    public static function lateAfterLabel(): string
    {
        $graceMinutes = (int) config('chicd.working_hours.grace_minutes');

        return config('chicd.working_hours.start').($graceMinutes > 0 ? " (with {$graceMinutes} minutes' grace)" : '');
    }

    /**
     * The public holidays in a month, keyed by date.
     *
     * @return Collection<string, PublicHoliday>
     */
    public static function holidaysIn(CarbonImmutable $month): Collection
    {
        return PublicHoliday::query()
            ->whereDate('date', '>=', $month->toDateString())
            ->whereDate('date', '<=', $month->endOfMonth()->toDateString())
            ->get()
            ->keyBy(fn (PublicHoliday $holiday): string => $holiday->date->toDateString());
    }

    /**
     * When work started each day: the earliest clock-in or field activity
     * start, in organisation time.
     *
     * @param  MonthlyTimesheet  $timesheet
     * @return array<string, CarbonImmutable>
     */
    private function firstStartByDate(array $timesheet): array
    {
        $timezone = config('chicd.timezone');
        $startedAt = [];

        foreach ($timesheet['days'] as $day) {
            $starts = [
                ...$day['officeAttendances']->map(fn ($attendance) => $attendance->clocked_in_at)->all(),
                ...$day['fieldActivities']->map(fn ($activity) => $activity->started_at)->all(),
            ];

            if ($starts !== []) {
                $startedAt[$day['date']] = CarbonImmutable::instance(min($starts))->setTimezone($timezone);
            }
        }

        return $startedAt;
    }

    /**
     * The leave type label for each date covered by approved leave.
     *
     * @param  Collection<int, LeaveRequest>  $approvedLeave
     * @return array<string, string>
     */
    private function leaveTypeByDate(Collection $approvedLeave): array
    {
        $types = [];

        foreach ($approvedLeave as $leave) {
            for ($day = $leave->starts_on; $day->lessThanOrEqualTo($leave->ends_on); $day = $day->addDay()) {
                $types[$day->toDateString()] = $leave->type->label();
            }
        }

        return $types;
    }
}
