<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Timesheets\BuildMonthlyTimesheet;
use App\Actions\Timesheets\SummariseAttendance;
use App\Concerns\ResolvesRequestedMonth;
use App\Enums\TimesheetStatus;
use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\LeaveRequest;
use App\Models\OfficeAttendance;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceOverviewController extends Controller
{
    use ResolvesRequestedMonth;

    /**
     * Show every staff member's attendance for a month, with their timesheet
     * status, and who is working right now.
     */
    public function index(Request $request, BuildMonthlyTimesheet $buildTimesheet, SummariseAttendance $summariseAttendance): Response
    {
        $month = $this->requestedMonth($request->string('month')->toString());
        $nextMonth = $month->addMonth();

        $officeByUser = OfficeAttendance::query()->startedBetween($month, $nextMonth)->with('office')->oldest('clocked_in_at')->oldest('id')->get()->groupBy('user_id');
        $fieldByUser = FieldActivity::query()->startedBetween($month, $nextMonth)->oldest('started_at')->oldest('id')->get()->groupBy('user_id');
        $reviews = Timesheet::query()->forMonth($month)->get()->keyBy('user_id');
        $pendingCorrections = CorrectionRequest::query()->pending()->forMonth($month)
            ->selectRaw('user_id, count(*) as aggregate')
            ->groupBy('user_id')
            ->pluck('aggregate', 'user_id');
        $leaveByUser = LeaveRequest::query()->approved()->overlapping($month, $month->endOfMonth())->get()->groupBy('user_id');
        $holidays = SummariseAttendance::holidaysIn($month);

        $staff = User::query()->employedDuring($month)->orderBy('name')->get()->map(function (User $user) use ($buildTimesheet, $summariseAttendance, $month, $officeByUser, $fieldByUser, $reviews, $pendingCorrections, $leaveByUser, $holidays): array {
            $timesheet = $buildTimesheet->fromRecords($month, $officeByUser->get($user->id, collect()), $fieldByUser->get($user->id, collect()));
            $status = $reviews->get($user->id)->status ?? TimesheetStatus::Pending;
            $attendance = $summariseAttendance->fromRecords($user, $month, $timesheet, $leaveByUser->get($user->id, collect()), $holidays);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->label(),
                'totals' => $timesheet['totals'],
                'status' => $status->value,
                'statusLabel' => $status->label(),
                'pendingCorrections' => (int) $pendingCorrections->get($user->id, 0),
                'attendance' => [
                    'workingDays' => $attendance['workingDays'],
                    'lateDays' => count($attendance['lateDays']),
                    'absentDays' => count($attendance['absentDays']),
                    'leaveDays' => count($attendance['leaveDays']),
                ],
            ];
        });

        return Inertia::render('admin/attendance/Index', [
            ...$this->monthNavigation($month),
            'staff' => $staff->all(),
            'workingNow' => $this->workingNow(),
            'pendingCorrectionsTotal' => CorrectionRequest::query()->pending()->count(),
        ]);
    }

    /**
     * Everyone with an office session or field activity still open.
     *
     * @return list<array{key: string, staffId: int, name: string, mode: string, since: string, where: string, flagged: bool}>
     */
    private function workingNow(): array
    {
        $timezone = config('chicd.timezone');

        $rows = [];

        foreach (OfficeAttendance::query()->open()->with(['user', 'office'])->get() as $attendance) {
            $rows[] = [
                'key' => 'office-'.$attendance->id,
                'staffId' => $attendance->user_id,
                'name' => $attendance->user->name,
                'mode' => 'Office',
                'since' => $attendance->clocked_in_at->setTimezone($timezone)->format('D j M, H:i'),
                'where' => $attendance->office->name ?? 'No office on record',
                'flagged' => ! $attendance->clocked_in_within_geofence,
                'sortKey' => $attendance->clocked_in_at->getTimestamp(),
            ];
        }

        foreach (FieldActivity::query()->open()->with('user')->get() as $activity) {
            $rows[] = [
                'key' => 'field-'.$activity->id,
                'staffId' => $activity->user_id,
                'name' => $activity->user->name,
                'mode' => 'Field',
                'since' => $activity->started_at->setTimezone($timezone)->format('D j M, H:i'),
                'where' => $activity->activity.' · '.$activity->location,
                'flagged' => false,
                'sortKey' => $activity->started_at->getTimestamp(),
            ];
        }

        usort($rows, fn (array $first, array $second): int => $first['sortKey'] <=> $second['sortKey']);

        return array_map(function (array $row): array {
            unset($row['sortKey']);

            return $row;
        }, $rows);
    }
}
