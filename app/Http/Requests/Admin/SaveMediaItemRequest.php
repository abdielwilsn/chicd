<?php

namespace App\Http\Requests\Admin;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class SaveMediaItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. The file is chosen
     * once, when the item is added; afterwards only its details change.
     * Photographs need a description for people using screen readers.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mediaItem = $this->route('mediaItem');
        $type = $mediaItem instanceof MediaItem ? $mediaItem->type : $this->enum('type', MediaType::class);

        return [
            'type' => [$mediaItem ? 'prohibited' : 'required', Rule::enum(MediaType::class)],
            'file' => $mediaItem ? ['prohibited'] : [
                'required',
                $type === MediaType::Video
                    ? File::types(['mp4', 'webm'])->max(50 * 1024)
                    : File::image()->max(8 * 1024),
            ],
            'poster' => [$type === MediaType::Video ? 'nullable' : 'prohibited', File::image()->max(4 * 1024)],
            'caption' => ['nullable', 'string', 'max:200'],
            'alt_text' => [$type === MediaType::Photo ? 'required' : 'nullable', 'string', 'max:200'],
            'show_in_hero' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
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
            'file.required' => 'Please choose the photo or video to upload.',
            'alt_text.required' => 'Describe the photo for people who cannot see it.',
        ];
    }
}
