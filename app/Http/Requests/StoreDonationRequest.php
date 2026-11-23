<?php

namespace App\Http\Requests;

use App\Actions\Donations\ListDonationFunds;
use App\Enums\DonationFrequency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request. The amount is in
     * whole naira.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fund' => ['required', 'string', Rule::in(array_keys(app(ListDonationFunds::class)->handle()))],
            'frequency' => ['required', Rule::enum(DonationFrequency::class)],
            'amount' => ['required', 'integer', 'min:'.config('chicd.donation_amounts.minimum'), 'max:'.config('chicd.donation_amounts.maximum')],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * The validated donation, typed for StartDonation. The amount is in whole
     * naira.
     *
     * @return array{fund: string, frequency: string, amount: int, name: string, email: string, phone: string|null}
     */
    public function donationDetails(): array
    {
        return [
            'fund' => $this->string('fund')->toString(),
            'frequency' => $this->string('frequency')->toString(),
            'amount' => $this->integer('amount'),
            'name' => $this->string('name')->trim()->toString(),
            'email' => $this->string('email')->trim()->toString(),
            'phone' => $this->filled('phone') ? $this->string('phone')->trim()->toString() : null,
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
            'fund.in' => 'Please choose what you would like to support.',
            'amount.required' => 'Please enter the amount you would like to give.',
            'amount.integer' => 'Please enter the amount in whole naira, without commas.',
            'amount.min' => 'The smallest gift we can take online is ₦:min.',
            'amount.max' => 'For a gift of this size, please contact us so we can arrange it with you.',
            'email.required' => 'Please give an email address so we can send your receipt.',
        ];
    }
}
