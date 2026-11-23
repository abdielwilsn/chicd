<?php

namespace Database\Factories;

use App\Enums\PayrollStatus;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollRecord>
 */
class PayrollRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'month' => now(config('chicd.timezone'))->subMonth()->format('Y-m-01'),
            'attendance_adjustment' => null,
            'status' => PayrollStatus::Pending,
        ];
    }

    /**
     * Pay an admin has approved, with the salary copied in.
     */
    public function approved(int $monthlySalary = 15000000): static
    {
        return $this->state(fn (array $attributes) => [
            'monthly_salary' => $monthlySalary,
            'status' => PayrollStatus::Approved,
            'approved_at' => now(),
        ]);
    }
}
