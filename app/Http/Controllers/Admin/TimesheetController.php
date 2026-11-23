<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Timesheets\BuildMonthlyTimesheet;
use App\Actions\Timesheets\DownloadTimesheets;
use App\Actions\Timesheets\SummariseAttendance;
use App\Concerns\ResolvesRequestedMonth;
use App\Enums\PayrollStatus;
use App\Enums\TimesheetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReopenTimesheetRequest;
use App\Http\Resources\AdminFieldActivityResource;
use App\Http\Resources\AdminOfficeAttendanceResource;
use App\Http\Resources\CorrectionRequestResource;
use App\Http\Resources\RecordChangeResource;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\RecordChange;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TimesheetController extends Controller
{
    use ResolvesRequestedMonth;

    public function __construct(private BuildMonthlyTimesheet $buildTimesheet, private SummariseAttendance $summariseAttendance) {}

    /**
     * Review one staff member's month: every record with the location captured,
     * their correction requests, and the audit trail.
     */
    public function show(User $user, string $month): Response
    {
        $month = $this->monthFromPath($month);
        $timesheet = $this->buildTimesheet->handle($user, $month);
        $review = $user->timesheets()->forMonth($month)->with('approver')->first();
        $correctionRequests = $user->correctionRequests()->forMonth($month)->with(['correctable', 'resolver', 'user'])->latest()->latest('id')->get();
        $timezone = config('chicd.timezone');

        return Inertia::render('admin/timesheets/Show', [
            ...$this->monthNavigation($month),
            'staff' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'timesheet' => [
                'status' => ($review->status ?? TimesheetStatus::Pending)->value,
                'label' => ($review->status ?? TimesheetStatus::Pending)->label(),
                'approvedAt' => $review?->isApproved() ? $review->approved_at?->setTimezone($timezone)->format('j F Y, H:i') : null,
                'approvedBy' => $review?->isApproved() ? $review->approver?->name : null,
                'approvalBlockers' => $this->approvalBlockers($month, $timesheet['totals']['openRecords'], $correctionRequests->filter->isPending()->count()),
            ],
            'totals' => $timesheet['totals'],
            'attendance' => $this->summariseAttendance->handle($user, $month, $timesheet),
            'lateAfter' => SummariseAttendance::lateAfterLabel(),
            'days' => array_map(fn (array $day): array => [
                'date' => $day['date'],
                'label' => $day['label'],
                'officeAttendances' => AdminOfficeAttendanceResource::collection($day['officeAttendances'])->resolve(),
                'fieldActivities' => AdminFieldActivityResource::collection($day['fieldActivities'])->resolve(),
            ], $timesheet['days']),
            'correctionRequests' => CorrectionRequestResource::collection($correctionRequests)->resolve(),
            'history' => RecordChangeResource::collection($this->history($timesheet, $review))->resolve(),
            'programmes' => config('chicd.field_programmes'),
        ]);
    }

    /**
     * Approve the month. A month can only be approved once it has ended, with
     * every record closed and every correction request dealt with.
     */
    public function approve(Request $request, User $user, string $month): RedirectResponse
    {
        $month = $this->monthFromPath($month);
        $timesheet = $this->buildTimesheet->handle($user, $month);
        $blockers = $this->approvalBlockers(
            $month,
            $timesheet['totals']['openRecords'],
            $user->correctionRequests()->pending()->forMonth($month)->count(),
        );

        if ($blockers !== []) {
            throw ValidationException::withMessages(['approval' => $blockers]);
        }

        DB::transaction(function () use ($request, $user, $month): void {
            $review = $user->timesheets()->forMonth($month)->first() ?? $user->timesheets()->make(['month' => $month->format('Y-m-01')]);

            abort_if($review->isApproved(), 409, 'This timesheet is already approved.');

            $review->fill([
                'status' => TimesheetStatus::Approved,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ])->save();

            $review->logChange($request->user(), 'approved', 'Timesheet approved.');
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name\'s timesheet for :month is approved.', ['name' => $user->name, 'month' => $month->format('F Y')])]);

        return back();
    }

    /**
     * Reopen an approved month so its records can be corrected again.
     */
    public function reopen(ReopenTimesheetRequest $request, User $user, string $month): RedirectResponse
    {
        $month = $this->monthFromPath($month);
        $review = $user->timesheets()->forMonth($month)->first();

        abort_unless($review?->isApproved(), 404);

        if ($user->payrollRecords()->forMonth($month)->whereNot('status', PayrollStatus::Pending)->exists()) {
            throw ValidationException::withMessages(['reason' => 'The pay for this month is already approved or paid. Move the pay back to Pending on the Payroll page first.']);
        }

        DB::transaction(function () use ($request, $review): void {
            $review->update(['status' => TimesheetStatus::Pending, 'approved_by' => null, 'approved_at' => null]);
            $review->logChange($request->user(), 'reopened', $request->string('reason')->toString());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Timesheet reopened for corrections.')]);

        return back();
    }

    /**
     * Download one staff member's timesheet, approved or not.
     */
    public function download(DownloadTimesheets $download, User $user, string $month, string $format): HttpResponse|StreamedResponse
    {
        $month = $this->monthFromPath($month);

        return $download->handle($format, $month, [[
            'user' => $user,
            'review' => $user->timesheets()->forMonth($month)->with('approver')->first(),
            'timesheet' => $this->buildTimesheet->handle($user, $month),
        ]], sprintf('chicd-timesheet-%s-%s', Str::slug($user->name), $month->format('Y-m')));
    }

    /**
     * Download every staff member's timesheet for a month in one file.
     */
    public function consolidated(DownloadTimesheets $download, string $month, string $format): HttpResponse|StreamedResponse
    {
        $month = $this->monthFromPath($month);
        $reviews = Timesheet::query()->forMonth($month)->with('approver')->get()->keyBy('user_id');

        $sheets = [];
        foreach (User::query()->employedDuring($month)->orderBy('name')->get() as $user) {
            $sheets[] = [
                'user' => $user,
                'review' => $reviews->get($user->id),
                'timesheet' => $this->buildTimesheet->handle($user, $month),
            ];
        }

        return $download->handle($format, $month, $sheets, 'chicd-consolidated-timesheet-'.$month->format('Y-m'));
    }

    /**
     * Why the month cannot be approved yet, if anything stops it.
     *
     * @return list<string>
     */
    private function approvalBlockers(CarbonImmutable $month, int $openRecords, int $pendingCorrections): array
    {
        return array_values(array_filter([
            $month->addMonth()->isFuture() ? 'The month has not ended yet.' : null,
            $openRecords > 0 ? trans_choice('{1} One record is still open. Correct it with an end time first.|[2,*] :count records are still open. Correct them with end times first.', $openRecords) : null,
            $pendingCorrections > 0 ? trans_choice('{1} One correction request is waiting for a decision.|[2,*] :count correction requests are waiting for a decision.', $pendingCorrections) : null,
        ]));
    }

    /**
     * Audit trail entries for the month's records and the timesheet itself.
     *
     * @param  array{days: list<array{officeAttendances: Collection<int, OfficeAttendance>, fieldActivities: Collection<int, FieldActivity>}>}  $timesheet
     * @return EloquentCollection<int, RecordChange>
     */
    private function history(array $timesheet, ?Timesheet $review): EloquentCollection
    {
        $officeIds = collect($timesheet['days'])->flatMap(fn (array $day) => $day['officeAttendances']->pluck('id'))->all();
        $fieldIds = collect($timesheet['days'])->flatMap(fn (array $day) => $day['fieldActivities']->pluck('id'))->all();

        return RecordChange::query()
            ->where(function (Builder $query) use ($officeIds, $fieldIds, $review): void {
                $query->where(fn (Builder $query) => $query->where('auditable_type', 'office_attendance')->whereIn('auditable_id', $officeIds))
                    ->orWhere(fn (Builder $query) => $query->where('auditable_type', 'field_activity')->whereIn('auditable_id', $fieldIds))
                    ->when($review, fn (Builder $query) => $query->orWhere(fn (Builder $query) => $query->where('auditable_type', 'timesheet')->where('auditable_id', $review->id)));
            })
            ->with(['auditable', 'changedBy'])
            ->latest('created_at')
            ->latest('id')
            ->get();
    }
}
