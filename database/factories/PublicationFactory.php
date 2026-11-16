<?php

namespace Database\Factories;

use App\Enums\PublicationCategory;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'category' => fake()->randomElement(PublicationCategory::cases()),
            'year' => fake()->numberBetween(2026, (int) now()->format('Y')),
            'summary' => fake()->paragraph(),
            'file_path' => 'publications/'.fake()->uuid().'.pdf',
            'file_size' => fake()->numberBetween(150_000, 8_000_000),
            'cover_image_path' => null,
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * An annual report.
     */
    public function annualReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => PublicationCategory::AnnualReport,
            'title' => 'Annual Report '.$attributes['year'],
        ]);
    }

    /**
     * A governance document for the Policies & Accountability page.
     */
    public function governance(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => PublicationCategory::Governance,
        ]);
    }

    /**
     * Mark the publication as the featured document for its category.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * A draft publication that is not yet visible to the public.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }
}
