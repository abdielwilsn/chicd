<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class SaveProgrammeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. A new programme
     * needs a photograph; an existing one keeps its photograph unless replaced.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isNew = $this->route('programme') === null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'summary' => ['required', 'string', 'max:2000'],
            'body' => ['nullable', 'string', 'max:20000'],
            'image' => [$isNew ? 'required' : 'nullable', File::image()->max(4 * 1024)],
            'image_alt' => ['nullable', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
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
            'image.required' => 'Please choose a photograph for the programme.',
        ];
    }
}
