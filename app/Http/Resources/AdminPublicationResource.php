<?php

namespace App\Http\Resources;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A publication as shown in the admin area: the public view plus whether it
 * is published, and the values for the edit form.
 *
 * @mixin Publication
 */
class AdminPublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...PublicationResource::make($this->resource)->resolve(),
            'is_published' => $this->isPublished(),
            'is_featured' => $this->is_featured,
            'published_on' => $this->published_at?->setTimezone(config('chicd.timezone'))->format('j M Y'),
            'updated_on' => $this->updated_at?->setTimezone(config('chicd.timezone'))->format('j M Y'),
        ];
    }
}
