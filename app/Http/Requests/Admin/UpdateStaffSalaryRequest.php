<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffSalaryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. The salary is in
     * naira; leave it empty to clear it.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'monthly_salary' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000000'],
        ];
    }

    /**
     * The salary in kobo, or null when cleared.
     */
    public function salaryInKobo(): ?int
    {
        return $this->filled('monthly_salary') ? (int) round((float) $this->input('monthly_salary') * 100) : null;
    }
}
