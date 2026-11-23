<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Programme>
 */
class ProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::title(fake()->unique()->word()).' Programme',
            'tagline' => fake()->sentence(5),
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'image_path' => 'programmes/'.fake()->uuid().'.jpg',
            'image_alt' => fake()->sentence(),
            'sort_order' => 0,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * A draft programme that is not yet visible to the public.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }
}
