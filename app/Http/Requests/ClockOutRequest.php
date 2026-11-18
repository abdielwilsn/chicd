<?php

namespace App\Http\Requests;

use App\Concerns\LocationValidationRules;
use App\Models\OfficeAttendance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ClockOutRequest extends FormRequest
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
     * Refuse to clock out of a session that is already closed.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var OfficeAttendance $officeAttendance */
                $officeAttendance = $this->route('officeAttendance');

                if (! $officeAttendance->isOpen()) {
                    $validator->errors()->add('attendance', 'You have already clocked out of this session.');
                }
            },
        ];
    }
}
