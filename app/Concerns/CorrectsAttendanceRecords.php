<?php

namespace App\Concerns;

use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\Timesheet;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Validator;

/**
 * Shared rules for an admin correcting an office session or field activity.
 * Times arrive from a datetime-local input in organisation time.
 */
trait CorrectsAttendanceRecords
{
    /**
     * The record being corrected, from the route.
     */
    abstract protected function correctedRecord(): OfficeAttendance|FieldActivity;

    /**
     * Rules for the reason and the optional correction request being answered.
     *
     * @return array<string, array<int, string>>
     */
    protected function correctionRules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'correction_request_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Please give a reason. It is kept in the audit trail and shown to the staff member.',
            '*.date_format' => 'Please enter a date and time.',
        ];
    }

    /**
     * A datetime-local value in organisation time, as a moment in storage time.
     */
    public function localTime(string $field): ?CarbonImmutable
    {
        $value = $this->input($field);

        return is_string($value) && $value !== ''
            ? CarbonImmutable::createFromFormat('Y-m-d\TH:i', $value, config('chicd.timezone'))->setTimezone(config('app.timezone'))
            : null;
    }

    /**
     * The pending request this correction answers, if one was chosen.
     */
    public function correctionRequest(): ?CorrectionRequest
    {
        if (! $this->filled('correction_request_id')) {
            return null;
        }

        return CorrectionRequest::query()
            ->pending()
            ->whereBelongsTo($this->correctedRecord()->user)
            ->find($this->integer('correction_request_id'));
    }

    /**
     * Checks that apply to both kinds of record: times are not in the future,
     * neither the old nor the new month is approved, and the chosen request
     * belongs to the same staff member and is still waiting.
     *
     * @param  list<string>  $timeFields
     */
    protected function validateCorrection(Validator $validator, array $timeFields, CarbonImmutable $originalStart, ?CarbonImmutable $newStart): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        foreach ($timeFields as $field) {
            if ($this->localTime($field)?->isFuture()) {
                $validator->errors()->add($field, 'The time cannot be in the future.');
            }
        }

        $owner = $this->correctedRecord()->user;

        if (Timesheet::isApprovedFor($owner, $originalStart) || ($newStart && Timesheet::isApprovedFor($owner, $newStart))) {
            $validator->errors()->add('record', 'This month\'s timesheet is approved. Reopen it before making corrections.');
        }

        if ($this->filled('correction_request_id') && $this->correctionRequest() === null) {
            $validator->errors()->add('correction_request_id', 'That correction request is not waiting for a decision.');
        }
    }
}
