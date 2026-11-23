<?php

namespace App\Models;

use App\Concerns\HasUniqueSlug;
use App\Concerns\Publishable;
use App\Enums\PostType;
use Carbon\CarbonImmutable;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A story, news article, event or announcement on News & Stories.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property PostType $type
 * @property string|null $excerpt
 * @property string $body
 * @property string|null $cover_image_path
 * @property string|null $cover_image_alt
 * @property CarbonImmutable|null $event_starts_at
 * @property string|null $event_location
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['title', 'type', 'excerpt', 'body', 'cover_image_alt', 'event_starts_at', 'event_location'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'event_starts_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    /**
     * Events that have not started yet.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function upcomingEvents(Builder $query): void
    {
        $query->where('type', PostType::Event)->where('event_starts_at', '>=', now());
    }
}
