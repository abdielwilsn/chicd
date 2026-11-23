<?php

namespace App\Http\Resources;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A team member as shown in the admin area.
 *
 * @mixin TeamMember
 */
class AdminTeamMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...TeamMemberResource::make($this->resource)->resolve(),
            'section_label' => $this->section->label(),
            'sort_order' => $this->sort_order,
            'is_published' => $this->isPublished(),
        ];
    }
}
