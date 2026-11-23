<?php

namespace App\Http\Requests\Admin;

use App\Enums\OpportunityType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class SaveOpportunityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', Rule::enum(OpportunityType::class)],
            'location' => ['nullable', 'string', 'max:150'],
            'summary' => ['required', 'string', 'max:1000'],
            'body' => ['nullable', 'string', 'max:20000'],
            'closes_on' => ['nullable', 'date_format:Y-m-d'],
            'application_url' => ['nullable', 'url:http,https', 'max:500'],
            'attachment' => ['nullable', File::types(['pdf', 'doc', 'docx'])->max(10 * 1024)],
            'remove_attachment' => ['boolean'],
            'is_published' => ['boolean'],
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
            'application_url.url' => 'Enter a full web address, starting with https://.',
        ];
    }
}
