<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\OfficeAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficeAttendance>
 */
class OfficeAttendanceFactory extends Factory
{
    /**
     * Define the model's default state: an open session clocked in at the office.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'office_id' => Office::factory(),
            'clocked_in_at' => now(),
            'clock_in_latitude' => 5.0377,
            'clock_in_longitude' => 7.9128,
            'clock_in_accuracy_metres' => 15,
            'clock_in_distance_metres' => 0,
            'clocked_in_within_geofence' => true,
        ];
    }

    /**
     * A session that has been clocked out at the office.
     */
    public function clockedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'clocked_out_at' => now(),
            'clock_out_latitude' => 5.0377,
            'clock_out_longitude' => 7.9128,
            'clock_out_accuracy_metres' => 15,
            'clock_out_distance_metres' => 0,
            'clocked_out_within_geofence' => true,
        ]);
    }
}
