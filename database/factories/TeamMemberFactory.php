<?php

namespace Database\Factories;

use App\Enums\TeamSection;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'section' => TeamSection::Team,
            'bio' => fake()->paragraph(),
            'photo_path' => null,
            'sort_order' => 0,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * A member of the executive leadership.
     */
    public function leadership(): static
    {
        return $this->state(fn (array $attributes) => [
            'section' => TeamSection::Leadership,
        ]);
    }

    /**
     * A team member hidden from the website.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }
}
