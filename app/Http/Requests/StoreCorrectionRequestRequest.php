<?php

namespace App\Http\Requests;

use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCorrectionRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'record_type' => ['nullable', 'string', Rule::in(['office_attendance', 'field_activity'])],
            'record_id' => ['nullable', 'required_with:record_type', 'integer'],
            'work_date' => ['required_without:record_type', 'nullable', 'date_format:Y-m-d', 'before_or_equal:'.now(config('chicd.timezone'))->toDateString()],
            'details' => ['required', 'string', 'max:2000'],
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
            'work_date.required_without' => 'Please choose the day the correction is about.',
            'work_date.before_or_equal' => 'The day cannot be in the future.',
            'details.required' => 'Please describe what needs correcting.',
        ];
    }

    /**
     * Only the staff member's own records can be the subject of a request.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->filled('record_type')) {
                    return;
                }

                if ($this->record() === null) {
                    $validator->errors()->add('record_id', 'That record could not be found.');
                }
            },
        ];
    }

    /**
     * The staff member's own office session or field activity named in the
     * request, if any.
     */
    public function record(): OfficeAttendance|FieldActivity|null
    {
        return match ($this->input('record_type')) {
            'office_attendance' => $this->user()->officeAttendances()->find($this->integer('record_id')),
            'field_activity' => $this->user()->fieldActivities()->find($this->integer('record_id')),
            default => null,
        };
    }
}
