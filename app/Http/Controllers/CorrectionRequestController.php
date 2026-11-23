<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorrectionRequestRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CorrectionRequestController extends Controller
{
    /**
     * Ask an admin to correct an office session, a field activity, or a day
     * with a record missing. Staff cannot change their records themselves.
     */
    public function store(StoreCorrectionRequestRequest $request): RedirectResponse
    {
        $record = $request->record();
        $timezone = config('chicd.timezone');

        $workDate = match (true) {
            $record instanceof OfficeAttendance => $record->clocked_in_at->setTimezone($timezone)->toDateString(),
            $record instanceof FieldActivity => $record->started_at->setTimezone($timezone)->toDateString(),
            default => $request->string('work_date')->toString(),
        };

        $correctionRequest = $request->user()->correctionRequests()->make([
            'work_date' => $workDate,
            'details' => $request->string('details')->toString(),
        ]);
        $correctionRequest->correctable()->associate($record);
        $correctionRequest->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Correction requested. An admin will review it.')]);

        return back();
    }
}
