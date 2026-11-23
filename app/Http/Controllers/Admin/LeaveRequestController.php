<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DecideLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    /**
     * Leave waiting for a decision, oldest first, recent decisions, and the
     * public holidays from the start of last year onwards.
     */
    public function index(): Response
    {
        return Inertia::render('admin/leave/Index', [
            'pending' => LeaveRequestResource::collection(
                LeaveRequest::query()->where('status', LeaveStatus::Pending)->with('user')->oldest()->oldest('id')->get()
            )->resolve(),
            'recentlyDecided' => LeaveRequestResource::collection(
                LeaveRequest::query()->whereIn('status', [LeaveStatus::Approved, LeaveStatus::Declined])->with(['user', 'decider'])->latest('decided_at')->latest('id')->limit(30)->get()
            )->resolve(),
            'holidays' => PublicHoliday::query()
                ->whereDate('date', '>=', now(config('chicd.timezone'))->subYear()->startOfYear()->toDateString())
                ->orderBy('date')
                ->get()
                ->map(fn (PublicHoliday $holiday): array => [
                    'id' => $holiday->id,
                    'date' => $holiday->date->toDateString(),
                    'label' => $holiday->date->format('D j M Y'),
                    'name' => $holiday->name,
                ]),
        ]);
    }

    /**
     * Approve or decline a leave request.
     */
    public function update(DecideLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless($leaveRequest->isPending(), 409, 'This request has already been decided or cancelled.');

        $status = LeaveStatus::from($request->string('status')->toString());
        $leaveRequest->decide($status, $request->user(), $request->filled('note') ? $request->string('note')->toString() : null);

        Inertia::flash('toast', ['type' => 'success', 'message' => $status === LeaveStatus::Approved
            ? __('Leave approved for :name.', ['name' => $leaveRequest->user->name])
            : __('Leave declined for :name.', ['name' => $leaveRequest->user->name])]);

        return back();
    }
}
