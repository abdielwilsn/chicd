<?php

namespace App\Http\Controllers;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    /**
     * The staff member's leave requests, newest first, and the form to ask
     * for leave.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('leave/Index', [
            'leaveRequests' => LeaveRequestResource::collection(
                $request->user()->leaveRequests()->with('decider')->latest('starts_on')->latest('id')->get()
            )->resolve(),
            'types' => collect(LeaveType::cases())->map(fn (LeaveType $type): array => ['value' => $type->value, 'label' => $type->label()]),
            'today' => now(config('chicd.timezone'))->toDateString(),
        ]);
    }

    /**
     * Ask for leave. An admin approves or declines it.
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $request->user()->leaveRequests()->create([
            'type' => LeaveType::from($request->string('type')->toString()),
            'starts_on' => $request->startsOn()->toDateString(),
            'ends_on' => $request->endsOn()->toDateString(),
            'reason' => $request->filled('reason') ? $request->string('reason')->toString() : null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Leave requested. An admin will review it.')]);

        return back();
    }

    /**
     * Withdraw a request that has not been decided yet.
     */
    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless($leaveRequest->isPending(), 409, 'This request has already been decided. Ask an admin if your plans have changed.');

        $leaveRequest->forceFill(['status' => LeaveStatus::Cancelled])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Leave request cancelled.')]);

        return back();
    }
}
