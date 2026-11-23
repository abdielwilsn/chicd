<?php

namespace Database\Factories;

use App\Enums\CorrectionStatus;
use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorrectionRequest>
 */
class CorrectionRequestFactory extends Factory
{
    /**
     * Define the model's default state: a pending request about a missing record.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'work_date' => now(config('chicd.timezone'))->toDateString(),
            'details' => fake()->sentence(12),
            'status' => CorrectionStatus::Pending,
        ];
    }
}
