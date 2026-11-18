<?php

namespace App\Http\Requests;

use App\Concerns\LocationValidationRules;
use App\Models\FieldActivity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EndFieldActivityRequest extends FormRequest
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
     * Refuse to end an activity that has already ended.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var FieldActivity $fieldActivity */
                $fieldActivity = $this->route('fieldActivity');

                if (! $fieldActivity->isOpen()) {
                    $validator->errors()->add('field_activity', 'This activity has already ended.');
                }
            },
        ];
    }
}
