<?php

namespace App\Http\Resources;

use App\Concerns\SplitsParagraphs;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

/**
 * @mixin Opportunity
 */
class OpportunityResource extends JsonResource
{
    use SplitsParagraphs;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'location' => $this->location,
            'summary' => $this->summary,
            'body' => $this->paragraphs($this->body),
            'closes_on' => $this->closes_on?->format('j F Y'),
            'is_closed' => $this->isClosed(),
            'application_url' => $this->application_url,
            'attachment_url' => $this->attachment_path ? Storage::disk('public')->url($this->attachment_path) : null,
            'attachment_size' => $this->attachment_size ? Number::fileSize($this->attachment_size, precision: 1) : null,
            'url' => route('get-involved.opportunity', $this->slug, absolute: false),
        ];
    }
}
