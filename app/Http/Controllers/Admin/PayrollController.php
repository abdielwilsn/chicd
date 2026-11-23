<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Timesheets\BuildMonthlyTimesheet;
use App\Actions\Timesheets\SummariseAttendance;
use App\Concerns\ResolvesRequestedMonth;
use App\Enums\PayrollStatus;
use App\Enums\TimesheetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePayrollAdjustmentRequest;
use App\Models\FieldActivity;
use App\Models\LeaveRequest;
use App\Models\OfficeAttendance;
use App\Models\PayrollRecord;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Monthly payroll: each staff member's salary, the attendance adjustment an
 * admin enters, and the approved pay, moving from Pending to Approved to
 * Paid. Admins only (the whole admin area needs the access-admin gate).
 */
class PayrollController extends Controller
{
    use ResolvesRequestedMonth;

    /**
     * Everyone's pay for a month, with their lateness, absence and leave as a
     * guide to the adjustment.
     */
    public function index(Request $request, BuildMonthlyTimesheet $buildTimesheet, SummariseAttendance $summariseAttendance): Response
    {
        $month = $this->requestedMonth($request->string('month')->toString());
        $nextMonth = $month->addMonth();

        $officeByUser = OfficeAttendance::query()->startedBetween($month, $nextMonth)->get()->groupBy('user_id');
        $fieldByUser = FieldActivity::query()->startedBetween($month, $nextMonth)->get()->groupBy('user_id');
        $leaveByUser = LeaveRequest::query()->approved()->overlapping($month, $month->endOfMonth())->get()->groupBy('user_id');
        $holidays = SummariseAttendance::holidaysIn($month);
        $reviews = Timesheet::query()->forMonth($month)->get()->keyBy('user_id');
        $records = PayrollRecord::query()->forMonth($month)->with(['approver:id,name', 'payer:id,name'])->get()->keyBy('user_id');

        $rows = User::query()->employedDuring($month)->orderBy('name')->get()->map(function (User $user) use ($buildTimesheet, $summariseAttendance, $month, $officeByUser, $fieldByUser, $leaveByUser, $holidays, $reviews, $records): array {
            $timesheet = $buildTimesheet->fromRecords($month, $officeByUser->get($user->id, collect()), $fieldByUser->get($user->id, collect()));
            $attendance = $summariseAttendance->fromRecords($user, $month, $timesheet, $leaveByUser->get($user->id, collect()), $holidays);
            $record = $records->get($user->id);
            $status = $record->status ?? PayrollStatus::Pending;
            $timesheetApproved = $reviews->get($user->id)?->isApproved() ?? false;
            $salary = $record->monthly_salary ?? $user->monthly_salary;
            $approvedPay = $record?->approvedPay($user->monthly_salary) ?? $user->monthly_salary;
            $timezone = config('chicd.timezone');

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'timesheetStatus' => ($timesheetApproved ? TimesheetStatus::Approved : TimesheetStatus::Pending)->value,
                'timesheetLabel' => ($timesheetApproved ? TimesheetStatus::Approved : TimesheetStatus::Pending)->label(),
                'attendance' => [
                    'workingDays' => $attendance['workingDays'],
                    'lateDays' => count($attendance['lateDays']),
                    'absentDays' => count($attendance['absentDays']),
                    'leaveDays' => count($attendance['leaveDays']),
                ],
                'monthlySalary' => $salary === null ? null : self::naira($salary),
                'adjustment' => $record?->attendance_adjustment === null ? null : $record->attendance_adjustment / 100,
                'adjustmentLabel' => $record?->attendance_adjustment ? self::naira($record->attendance_adjustment) : null,
                'adjustmentNote' => $record?->adjustment_note,
                'approvedPay' => $approvedPay === null ? null : self::naira($approvedPay),
                'status' => $status->value,
                'statusLabel' => $status->label(),
                'approvedBy' => $record?->approver?->name,
                'approvedOn' => $record?->approved_at?->setTimezone($timezone)->format('j M Y'),
                'paidBy' => $record?->payer?->name,
                'paidOn' => $record?->paid_at?->setTimezone($timezone)->format('j M Y'),
                'approvalBlockers' => $status === PayrollStatus::Pending ? $this->approvalBlockers($user, $timesheetApproved) : [],
            ];
        });

        return Inertia::render('admin/payroll/Index', [
            ...$this->monthNavigation($month),
            'staff' => $rows->all(),
            'totals' => [
                'pending' => $rows->where('status', PayrollStatus::Pending->value)->count(),
                'approved' => $rows->where('status', PayrollStatus::Approved->value)->count(),
                'paid' => $rows->where('status', PayrollStatus::Paid->value)->count(),
            ],
        ]);
    }

    /**
     * Set the attendance adjustment while the pay is still pending.
     */
    public function update(UpdatePayrollAdjustmentRequest $request, User $user, string $month): RedirectResponse
    {
        $record = $this->recordFor($user, $this->monthFromPath($month));

        abort_unless($record->status === PayrollStatus::Pending, 409, 'Move the pay back to Pending before changing it.');

        $adjustment = $request->adjustmentInKobo();
        $record->fill([
            'attendance_adjustment' => $adjustment === 0 ? null : $adjustment,
            'adjustment_note' => $adjustment === 0 ? null : $request->string('note')->trim()->toString(),
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Adjustment saved for :name.', ['name' => $user->name])]);

        return back();
    }

    /**
     * Approve the pay, copying in the current salary. The month's timesheet
     * must be approved and a salary set.
     */
    public function approve(Request $request, User $user, string $month): RedirectResponse
    {
        $month = $this->monthFromPath($month);
        $record = $this->recordFor($user, $month);

        abort_unless($record->status === PayrollStatus::Pending, 409, 'This pay has already been approved.');

        $blockers = $this->approvalBlockers($user, Timesheet::query()->whereBelongsTo($user)->forMonth($month)->first()?->isApproved() ?? false);

        if ($blockers !== []) {
            throw ValidationException::withMessages(['payroll' => $blockers]);
        }

        $record->forceFill([
            'monthly_salary' => $user->monthly_salary,
            'status' => PayrollStatus::Approved,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pay approved for :name.', ['name' => $user->name])]);

        return back();
    }

    /**
     * Move approved pay back to Pending so it can be changed. Paid pay
     * cannot be moved back.
     */
    public function reopen(User $user, string $month): RedirectResponse
    {
        $record = $this->recordFor($user, $this->monthFromPath($month));

        abort_unless($record->status === PayrollStatus::Approved, 409, 'Only approved pay that has not been paid can be moved back to Pending.');

        $record->forceFill([
            'monthly_salary' => null,
            'status' => PayrollStatus::Pending,
            'approved_by' => null,
            'approved_at' => null,
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name\'s pay is back to Pending.', ['name' => $user->name])]);

        return back();
    }

    /**
     * Record that approved pay has been paid.
     */
    public function markPaid(Request $request, User $user, string $month): RedirectResponse
    {
        $record = $this->recordFor($user, $this->monthFromPath($month));

        abort_unless($record->status === PayrollStatus::Approved, 409, 'Only approved pay can be marked as paid.');

        $record->forceFill([
            'status' => PayrollStatus::Paid,
            'paid_by' => $request->user()->id,
            'paid_at' => now(),
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name\'s pay is marked as paid.', ['name' => $user->name])]);

        return back();
    }

    /**
     * The staff member's payroll record for the month, made if needed.
     */
    private function recordFor(User $user, CarbonImmutable $month): PayrollRecord
    {
        return $user->payrollRecords()->forMonth($month)->first()
            ?? $user->payrollRecords()->make(['month' => $month->format('Y-m-01')]);
    }

    /**
     * An amount in kobo formatted in naira, such as "₦150,000.00".
     */
    private static function naira(int $kobo): string
    {
        return Number::currency($kobo / 100, in: 'NGN', locale: 'en_NG') ?: '₦'.number_format($kobo / 100, 2);
    }

    /**
     * Why the pay cannot be approved yet, if anything stops it.
     *
     * @return list<string>
     */
    private function approvalBlockers(User $user, bool $timesheetApproved): array
    {
        return array_values(array_filter([
            $timesheetApproved ? null : 'The timesheet for this month is not approved yet.',
            $user->monthly_salary === null ? 'No monthly salary is set. Set it on the Staff accounts page.' : null,
        ]));
    }
}
