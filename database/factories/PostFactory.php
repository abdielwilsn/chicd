<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'type' => PostType::News,
            'excerpt' => fake()->sentence(18),
            'body' => fake()->paragraphs(4, true),
            'cover_image_path' => null,
            'cover_image_alt' => null,
            'event_starts_at' => null,
            'event_location' => null,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * An event starting at the given time, next week by default.
     */
    public function event(?string $startsAt = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PostType::Event,
            'event_starts_at' => $startsAt ?? now()->addWeek(),
            'event_location' => 'Uyo, Akwa Ibom State',
        ]);
    }

    /**
     * A draft post that is not yet visible to the public.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }
}
