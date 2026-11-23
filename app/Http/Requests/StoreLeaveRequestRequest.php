<?php

namespace App\Http\Requests;

use App\Enums\LeaveType;
use App\Models\Timesheet;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. Leave may be in the
     * past (sick leave is often recorded afterwards) but not more than a year
     * ahead.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(LeaveType::class)],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on', 'before_or_equal:'.now(config('chicd.timezone'))->addYear()->toDateString()],
            'reason' => ['nullable', 'string', 'max:2000'],
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
            'ends_on.after_or_equal' => 'The last day of leave cannot be before the first day.',
            'ends_on.before_or_equal' => 'Leave can be requested up to a year ahead.',
        ];
    }

    /**
     * Refuse leave that overlaps leave already requested or approved, or that
     * falls in a month whose timesheet is approved.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $startsOn = $this->startsOn();
                $endsOn = $this->endsOn();

                if ($this->user()->leaveRequests()->active()->overlapping($startsOn, $endsOn)->exists()) {
                    $validator->errors()->add('starts_on', 'You already have leave requested or approved for some of these days.');
                } elseif (Timesheet::anyApprovedBetween($this->user(), $startsOn, $endsOn)) {
                    $validator->errors()->add('starts_on', 'Your timesheet for this month is already approved. Ask an admin to reopen it first.');
                }
            },
        ];
    }

    /**
     * The first day of leave.
     */
    public function startsOn(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $this->string('starts_on')->toString(), config('chicd.timezone'));
    }

    /**
     * The last day of leave.
     */
    public function endsOn(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $this->string('ends_on')->toString(), config('chicd.timezone'));
    }
}
