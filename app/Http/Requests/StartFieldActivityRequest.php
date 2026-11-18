<?php

namespace App\Http\Requests;

use App\Concerns\LocationValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StartFieldActivityRequest extends FormRequest
{
    use LocationValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'programme' => ['required', 'string', Rule::in(config('chicd.field_programmes'))],
            'activity' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:160'],
            ...$this->locationRules(),
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
            'programme.in' => 'Please choose a programme from the list.',
            'location.required' => 'Please say where the activity is taking place.',
            ...$this->locationMessages(),
        ];
    }

    /**
     * Refuse to start an activity while another one is still running.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->user()->fieldActivities()->open()->exists()) {
                    $validator->errors()->add('field_activity', 'Please end your current activity before starting another.');
                }
            },
        ];
    }
}
