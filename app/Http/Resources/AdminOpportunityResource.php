<?php

namespace App\Http\Resources;

use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An opportunity as shown in the admin area: the public view plus the raw
 * text for the edit form and whether it is published.
 *
 * @mixin Opportunity
 */
class AdminOpportunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...OpportunityResource::make($this->resource)->resolve(),
            'body_text' => $this->body,
            'closes_on_date' => $this->closes_on?->toDateString(),
            'is_published' => $this->isPublished(),
            'published_on' => $this->published_at?->setTimezone(config('chicd.timezone'))->format('j M Y'),
        ];
    }
}
