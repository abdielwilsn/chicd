<?php

namespace App\Http\Requests\Admin;

use App\Enums\PublicationCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class SavePublicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. A new publication
     * needs a document; an existing one keeps its document unless replaced.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isNew = $this->route('publication') === null;

        return [
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::enum(PublicationCategory::class)],
            'year' => ['required', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'summary' => ['nullable', 'string', 'max:2000'],
            'file' => [$isNew ? 'required' : 'nullable', File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])->max(20 * 1024)],
            'cover_image' => ['nullable', File::image()->max(4 * 1024)],
            'remove_cover_image' => ['boolean'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
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
            'file.required' => 'Please choose the document to upload.',
        ];
    }
}
