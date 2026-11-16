<?php

namespace Database\Factories;

use App\Enums\EnquiryType;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => EnquiryType::General,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'organisation' => null,
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
        ];
    }

    /**
     * A confidential Safe Housing enquiry.
     */
    public function safeHousing(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EnquiryType::SafeHousing,
            'subject' => null,
        ]);
    }
}
