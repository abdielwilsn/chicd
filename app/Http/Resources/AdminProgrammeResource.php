<?php

namespace App\Http\Resources;

use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A programme as shown in the admin area: the public view plus the raw text
 * for the edit form and whether it is published.
 *
 * @mixin Programme
 */
class AdminProgrammeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...ProgrammeResource::make($this->resource)->resolve(),
            'summary_text' => $this->summary,
            'body_text' => $this->body,
            'image_alt' => $this->image_alt,
            'sort_order' => $this->sort_order,
            'is_published' => $this->isPublished(),
            'published_on' => $this->published_at?->setTimezone(config('chicd.timezone'))->format('j M Y'),
        ];
    }
}
