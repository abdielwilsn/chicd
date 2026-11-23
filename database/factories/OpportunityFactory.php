<?php

namespace Database\Factories;

use App\Enums\OpportunityType;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'type' => fake()->randomElement(OpportunityType::cases()),
            'location' => 'Uyo, Akwa Ibom State',
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'closes_on' => now()->addMonth()->toDateString(),
            'application_url' => null,
            'attachment_path' => null,
            'attachment_size' => null,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * An opportunity whose closing date has passed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'closes_on' => now()->subWeek()->toDateString(),
        ]);
    }

    /**
     * A draft opportunity that is not yet visible to the public.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }
}
