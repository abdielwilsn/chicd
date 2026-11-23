<?php

namespace App\Http\Resources;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin TeamMember
 */
class TeamMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, position: string, section: string, bio: string|null, photo_url: string|null, initials: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'section' => $this->section->value,
            'bio' => $this->bio,
            'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
            'initials' => Str::of($this->name)->explode(' ')->filter()->take(2)
                ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))->implode(''),
        ];
    }
}
