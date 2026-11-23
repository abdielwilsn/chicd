<?php

namespace App\Http\Controllers;

use App\Actions\Timesheets\BuildMonthlyTimesheet;
use App\Actions\Timesheets\SummariseAttendance;
use App\Concerns\ResolvesRequestedMonth;
use App\Enums\TimesheetStatus;
use App\Http\Resources\CorrectionRequestResource;
use App\Http\Resources\FieldActivityResource;
use App\Http\Resources\OfficeAttendanceResource;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceHistoryController extends Controller
{
    use ResolvesRequestedMonth;

    /**
     * Show the staff member's own office sessions and field activities for
     * one month, grouped by day, with the month's timesheet status and any
     * correction requests they have made.
     */
    public function index(Request $request, BuildMonthlyTimesheet $buildTimesheet, SummariseAttendance $summariseAttendance): Response
    {
        $month = $this->requestedMonth($request->string('month')->toString());
        $user = $request->user();
        $timesheet = $buildTimesheet->handle($user, $month);
        $review = $user->timesheets()->forMonth($month)->with('approver')->first();

        return Inertia::render('attendance/Index', [
            ...$this->monthNavigation($month),
            'days' => collect($timesheet['days'])
                ->reverse()
                ->map(fn (array $day) => [
                    'date' => $day['date'],
                    'label' => $day['label'],
                    'officeAttendances' => OfficeAttendanceResource::collection($day['officeAttendances'])->resolve(),
                    'fieldActivities' => FieldActivityResource::collection($day['fieldActivities'])->resolve(),
                ])
                ->values()
                ->all(),
            'totals' => [
                'officeMinutes' => $timesheet['totals']['officeMinutes'],
                'fieldMinutes' => $timesheet['totals']['fieldMinutes'],
                'officeDays' => $timesheet['totals']['officeDays'],
                'fieldDays' => $timesheet['totals']['fieldDays'],
            ],
            'attendance' => $summariseAttendance->handle($user, $month, $timesheet),
            'lateAfter' => SummariseAttendance::lateAfterLabel(),
            'timesheet' => $this->timesheetStatus($review),
            'today' => now(config('chicd.timezone'))->toDateString(),
            'correctionRequests' => CorrectionRequestResource::collection(
                $user->correctionRequests()->forMonth($month)->with('correctable')->latest()->latest('id')->get()
            )->resolve(),
        ]);
    }

    /**
     * The month's review status as shown to the staff member.
     *
     * @return array{status: string, label: string, approvedAt: string|null, approvedBy: string|null}
     */
    private function timesheetStatus(?Timesheet $review): array
    {
        $status = $review->status ?? TimesheetStatus::Pending;

        return [
            'status' => $status->value,
            'label' => $status->label(),
            'approvedAt' => $review?->isApproved() ? $review->approved_at?->setTimezone(config('chicd.timezone'))->format('j F Y') : null,
            'approvedBy' => $review?->isApproved() ? $review->approver?->name : null,
        ];
    }
}
