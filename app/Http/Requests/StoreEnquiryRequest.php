<?php

namespace App\Http\Requests;

use App\Enums\EnquiryType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Safe Housing enquiries may be sent with a phone number instead of an
     * email address, so people without email can still reach CHICD.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isSafeHousing = $this->input('type') === EnquiryType::SafeHousing->value;

        return [
            'type' => ['required', Rule::enum(EnquiryType::class)],
            'name' => ['required', 'string', 'max:120'],
            'email' => [$isSafeHousing ? 'required_without:phone' : 'required', 'nullable', 'email', 'max:255'],
            'phone' => [$isSafeHousing ? 'required_without:email' : 'nullable', 'nullable', 'string', 'max:30'],
            'organisation' => ['nullable', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['prohibited'],
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
            'email.required_without' => 'Please give us an email address or a phone number so we can reply.',
            'phone.required_without' => 'Please give us a phone number or an email address so we can reply.',
        ];
    }
}
