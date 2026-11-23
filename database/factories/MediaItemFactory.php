<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => MediaType::Photo,
            'caption' => fake()->sentence(),
            'alt_text' => fake()->sentence(),
            'file_path' => 'gallery/'.fake()->uuid().'.jpg',
            'file_size' => fake()->numberBetween(100_000, 3_000_000),
            'poster_path' => null,
            'show_in_hero' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * A video rather than a photograph.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::Video,
            'alt_text' => null,
            'file_path' => 'gallery/'.fake()->uuid().'.mp4',
        ]);
    }

    /**
     * Chosen to show in the home page hero.
     */
    public function inHero(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_hero' => true,
        ]);
    }
}
