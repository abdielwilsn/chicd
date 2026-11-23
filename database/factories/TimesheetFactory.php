<?php

namespace Database\Factories;

use App\Enums\TimesheetStatus;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timesheet>
 */
class TimesheetFactory extends Factory
{
    /**
     * Define the model's default state: last month, awaiting approval.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'month' => now(config('chicd.timezone'))->subMonthNoOverflow()->format('Y-m-01'),
            'status' => TimesheetStatus::Pending,
        ];
    }

    /**
     * A timesheet an admin has approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TimesheetStatus::Approved,
            'approved_by' => User::factory()->admin(),
            'approved_at' => now(),
        ]);
    }
}
