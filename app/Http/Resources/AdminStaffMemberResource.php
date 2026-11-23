<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A staff portal account as listed for admins.
 *
 * @mixin User
 */
class AdminStaffMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'is_current_user' => $this->resource->is($request->user()),
            'has_two_factor' => $this->two_factor_confirmed_at !== null,
            'added_on' => $this->created_at?->setTimezone(config('chicd.timezone'))->format('j M Y'),
            'is_active' => $this->isActive(),
            'monthly_salary' => $this->monthly_salary === null ? null : $this->monthly_salary / 100,
            'deactivated_on' => $this->deactivated_at?->setTimezone(config('chicd.timezone'))->format('j M Y'),
            'deactivated_by' => $this->whenLoaded('deactivatedBy', fn () => $this->deactivatedBy?->name),
        ];
    }
}
