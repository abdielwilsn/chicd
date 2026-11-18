<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation for the one-off device location sent when staff clock in or out,
 * or start or end a field activity.
 */
trait LocationValidationRules
{
    /**
     * Get the validation rules for a device location reading.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function locationRules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get the messages shown when the device location is missing.
     *
     * @return array<string, string>
     */
    protected function locationMessages(): array
    {
        $message = 'We could not read your location. Please allow location access and try again.';

        return [
            'latitude.required' => $message,
            'longitude.required' => $message,
        ];
    }

    /**
     * The validated location, mapped to column values with the given prefix.
     *
     * @return array<string, float|int|null>
     */
    public function locationAttributes(string $prefix): array
    {
        $accuracy = $this->validated('accuracy');

        return [
            "{$prefix}_latitude" => (float) $this->validated('latitude'),
            "{$prefix}_longitude" => (float) $this->validated('longitude'),
            "{$prefix}_accuracy_metres" => $accuracy === null ? null : (int) round((float) $accuracy),
        ];
    }
}
