<?php

namespace Database\Factories;

use App\Models\FieldActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FieldActivity>
 */
class FieldActivityFactory extends Factory
{
    /**
     * Define the model's default state: an activity that has started but not ended.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'programme' => 'Show Up Ready to Learn (SURL)',
            'activity' => 'School uniform distribution',
            'description' => fake()->sentence(),
            'location' => 'Ikot Ekpene',
            'started_at' => now(),
            'start_latitude' => 5.1794,
            'start_longitude' => 7.7144,
            'start_accuracy_metres' => 20,
        ];
    }

    /**
     * An activity that has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => now(),
            'end_latitude' => 5.1794,
            'end_longitude' => 7.7144,
            'end_accuracy_metres' => 20,
        ]);
    }
}
