<?php

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsOn = now()->addWeek()->startOfWeek();

        return [
            'user_id' => User::factory(),
            'type' => LeaveType::Annual,
            'starts_on' => $startsOn->toDateString(),
            'ends_on' => $startsOn->addDays(4)->toDateString(),
            'reason' => fake()->sentence(),
            'status' => LeaveStatus::Pending,
        ];
    }

    /**
     * Leave an admin has approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaveStatus::Approved,
            'decided_at' => now(),
        ]);
    }
}
