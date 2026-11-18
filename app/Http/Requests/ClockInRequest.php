<?php

namespace App\Http\Requests;

use App\Concerns\LocationValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ClockInRequest extends FormRequest
{
    use LocationValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->locationRules();
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->locationMessages();
    }

    /**
     * Refuse a second clock-in while an office session is still open.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->user()->officeAttendances()->open()->exists()) {
                    $validator->errors()->add('attendance', 'You are already clocked in. Clock out before clocking in again.');
                }
            },
        ];
    }
}
