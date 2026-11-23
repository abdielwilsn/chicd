<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CorrectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFieldActivityRecordRequest;
use App\Http\Requests\Admin\UpdateOfficeAttendanceRecordRequest;
use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AttendanceRecordController extends Controller
{
    /**
     * Correct an office session's times. The location captured by the staff
     * member is kept as it was.
     */
    public function updateOfficeAttendance(UpdateOfficeAttendanceRecordRequest $request, OfficeAttendance $officeAttendance): RedirectResponse
    {
        $officeAttendance->fill([
            'clocked_in_at' => $request->localTime('clocked_in_at'),
            'clocked_out_at' => $request->localTime('clocked_out_at'),
        ]);

        return $this->saveCorrection($officeAttendance, $request->user(), $request->string('reason')->toString(), $request->correctionRequest());
    }

    /**
     * Correct a field activity's times and details. The location captured by
     * the staff member is kept as it was.
     */
    public function updateFieldActivity(UpdateFieldActivityRecordRequest $request, FieldActivity $fieldActivity): RedirectResponse
    {
        $fieldActivity->fill([
            'started_at' => $request->localTime('started_at'),
            'ended_at' => $request->localTime('ended_at'),
            'programme' => $request->string('programme')->toString(),
            'activity' => $request->string('activity')->toString(),
            'location' => $request->string('location')->toString(),
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
        ]);

        return $this->saveCorrection($fieldActivity, $request->user(), $request->string('reason')->toString(), $request->correctionRequest());
    }

    /**
     * Save the record, add the change to its audit trail, and close the
     * correction request it answers.
     */
    private function saveCorrection(OfficeAttendance|FieldActivity $record, User $admin, string $reason, ?CorrectionRequest $correctionRequest): RedirectResponse
    {
        $changes = $this->describeChanges($record);

        if ($changes === []) {
            Inertia::flash('toast', ['type' => 'warning', 'message' => __('Nothing was changed.')]);

            return back();
        }

        DB::transaction(function () use ($record, $admin, $reason, $correctionRequest, $changes): void {
            $record->save();
            $record->logChange($admin, 'corrected', $reason, $changes, $correctionRequest);
            $correctionRequest?->close(CorrectionStatus::Resolved, $admin, $reason);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Record corrected. The change is in the audit trail.')]);

        return back();
    }

    /**
     * The changed attributes, old and new, with times in organisation time.
     *
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function describeChanges(Model $record): array
    {
        $changes = [];

        foreach (array_keys($record->getDirty()) as $attribute) {
            $changes[$attribute] = [
                'from' => $this->displayValue($record->getOriginal($attribute)),
                'to' => $this->displayValue($record->getAttribute($attribute)),
            ];
        }

        return $changes;
    }

    /**
     * A value as it should read in the audit trail.
     */
    private function displayValue(mixed $value): mixed
    {
        return $value instanceof CarbonInterface
            ? $value->setTimezone(config('chicd.timezone'))->format('Y-m-d H:i')
            : $value;
    }
}
