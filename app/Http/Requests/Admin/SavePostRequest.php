<?php

namespace App\Http\Requests\Admin;

use App\Enums\PostType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class SavePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. Events need a start
     * date and time, entered in the organisation timezone.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', Rule::enum(PostType::class)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'cover_image' => ['nullable', File::image()->max(4 * 1024)],
            'cover_image_alt' => ['nullable', 'string', 'max:200'],
            'remove_cover_image' => ['boolean'],
            'event_starts_at' => [Rule::requiredIf($this->input('type') === PostType::Event->value), 'nullable', 'date_format:Y-m-d\TH:i'],
            'event_location' => ['nullable', 'string', 'max:200'],
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
            'event_starts_at.required' => 'Please enter when the event starts.',
        ];
    }
}
