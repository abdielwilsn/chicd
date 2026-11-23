<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollAdjustmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. The adjustment is
     * in naira, negative for a deduction, and needs a note unless it is zero.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'adjustment' => ['nullable', 'numeric', 'decimal:0,2', 'between:-100000000,100000000'],
            'note' => [$this->adjustmentInKobo() !== 0 ? 'required' : 'nullable', 'nullable', 'string', 'max:1000'],
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
            'adjustment.numeric' => 'Enter the adjustment in naira, for example -5000 for a deduction.',
            'note.required' => 'Please note the reason for the adjustment, for example "2 unexplained absences".',
        ];
    }

    /**
     * The adjustment in kobo.
     */
    public function adjustmentInKobo(): int
    {
        $adjustment = $this->input('adjustment');

        return is_numeric($adjustment) ? (int) round((float) $adjustment * 100) : 0;
    }
}
