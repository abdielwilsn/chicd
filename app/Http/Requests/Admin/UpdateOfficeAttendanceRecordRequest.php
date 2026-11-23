<?php

namespace App\Http\Requests\Admin;

use App\Concerns\CorrectsAttendanceRecords;
use App\Models\OfficeAttendance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOfficeAttendanceRecordRequest extends FormRequest
{
    use CorrectsAttendanceRecords;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clocked_in_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'clocked_out_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:clocked_in_at'],
            ...$this->correctionRules(),
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateCorrection(
                $validator,
                ['clocked_in_at', 'clocked_out_at'],
                $this->correctedRecord()->clocked_in_at,
                $this->localTime('clocked_in_at'),
            ),
        ];
    }

    /**
     * The office session from the route.
     */
    protected function correctedRecord(): OfficeAttendance
    {
        /** @var OfficeAttendance $attendance */
        $attendance = $this->route('officeAttendance');

        return $attendance;
    }
}
