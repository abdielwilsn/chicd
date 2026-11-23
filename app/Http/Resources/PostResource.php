<?php

namespace App\Http\Resources;

use App\Concerns\SplitsParagraphs;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A story, news article, event or announcement. Dates are formatted in the
 * organisation timezone.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
{
    use SplitsParagraphs;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $timezone = config('chicd.timezone');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'excerpt' => $this->excerpt ?? Str::limit(Str::squish($this->body), 220),
            'body' => $this->paragraphs($this->body),
            'cover_image' => $this->cover_image_path ? [
                'src' => Storage::disk('public')->url($this->cover_image_path),
                'alt' => $this->cover_image_alt ?? '',
            ] : null,
            'event_starts' => $this->event_starts_at?->setTimezone($timezone)->format('l j F Y, g:i a'),
            'event_day' => $this->event_starts_at?->setTimezone($timezone)->format('j'),
            'event_month' => $this->event_starts_at?->setTimezone($timezone)->format('M'),
            'event_location' => $this->event_location,
            'published_on' => $this->published_at?->setTimezone($timezone)->format('j F Y'),
            'url' => route('resources.post', $this->slug, absolute: false),
        ];
    }
}
