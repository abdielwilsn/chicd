<?php

namespace App\Http\Requests\Admin;

use App\Enums\TeamSection;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class SaveTeamMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'position' => ['required', 'string', 'max:150'],
            'section' => ['required', Rule::enum(TeamSection::class)],
            'bio' => ['nullable', 'string', 'max:1500'],
            'photo' => ['nullable', File::image()->max(4 * 1024)],
            'remove_photo' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_published' => ['boolean'],
        ];
    }
}
