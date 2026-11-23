<?php

namespace App\Http\Requests\Admin;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\Timesheet;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DecideLeaveRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. Declining needs a
     * note for the staff member.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([LeaveStatus::Approved->value, LeaveStatus::Declined->value])],
            'note' => ['required_if:status,'.LeaveStatus::Declined->value, 'nullable', 'string', 'max:2000'],
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
            'note.required_if' => 'Please tell the staff member why their leave was declined.',
        ];
    }

    /**
     * Leave cannot be approved into a month whose timesheet is already
     * approved: the month would change after it was signed off.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var LeaveRequest $leaveRequest */
                $leaveRequest = $this->route('leaveRequest');

                if ($this->input('status') === LeaveStatus::Approved->value
                    && Timesheet::anyApprovedBetween($leaveRequest->user, $leaveRequest->starts_on, $leaveRequest->ends_on)) {
                    $validator->errors()->add('status', 'The timesheet for this month is already approved. Reopen it before approving this leave.');
                }
            },
        ];
    }
}
