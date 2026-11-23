<?php

namespace App\Http\Requests\Admin;

use App\Models\PublicHoliday;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePublicHolidayRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'name' => ['required', 'string', 'max:120'],
        ];
    }

    /**
     * Refuse a day that is already a holiday. Checked by date rather than with
     * the unique rule, because dates are stored with a time on some databases.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isEmpty() && PublicHoliday::query()->whereDate('date', $this->string('date')->toString())->exists()) {
                    $validator->errors()->add('date', 'This day is already a public holiday.');
                }
            },
        ];
    }
}
