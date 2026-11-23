<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A post as shown in the admin area: the public view plus the raw values for
 * the edit form and whether it is published.
 *
 * @mixin Post
 */
class AdminPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...PostResource::make($this->resource)->resolve(),
            'excerpt_text' => $this->excerpt,
            'body_text' => $this->body,
            'cover_image_alt' => $this->cover_image_alt,
            'event_starts_at' => $this->event_starts_at?->setTimezone(config('chicd.timezone'))->format('Y-m-d\TH:i'),
            'is_published' => $this->isPublished(),
        ];
    }
}
