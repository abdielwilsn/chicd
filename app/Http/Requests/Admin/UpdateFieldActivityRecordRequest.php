<?php

namespace App\Http\Requests\Admin;

use App\Concerns\CorrectsAttendanceRecords;
use App\Models\FieldActivity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFieldActivityRecordRequest extends FormRequest
{
    use CorrectsAttendanceRecords;

    /**
     * Get the validation rules that apply to the request. The activity's
     * current programme stays valid even if it has left the list.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'started_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ended_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:started_at'],
            'programme' => ['required', 'string', Rule::in([...config('chicd.field_programmes'), $this->correctedRecord()->programme])],
            'activity' => ['required', 'string', 'max:160'],
            'location' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
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
                ['started_at', 'ended_at'],
                $this->correctedRecord()->started_at,
                $this->localTime('started_at'),
            ),
        ];
    }

    /**
     * The field activity from the route.
     */
    protected function correctedRecord(): FieldActivity
    {
        /** @var FieldActivity $activity */
        $activity = $this->route('fieldActivity');

        return $activity;
    }
}
