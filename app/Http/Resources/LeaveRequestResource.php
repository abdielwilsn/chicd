<?php

namespace App\Http\Resources;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A leave request, for the staff member who made it or for admins.
 *
 * @mixin LeaveRequest
 */
class LeaveRequestResource extends JsonResource
{
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
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'starts_on' => $this->starts_on->toDateString(),
            'ends_on' => $this->ends_on->toDateString(),
            'dates' => $this->starts_on->equalTo($this->ends_on)
                ? $this->starts_on->format('D j M Y')
                : $this->starts_on->format('D j M').' – '.$this->ends_on->format('D j M Y'),
            'calendar_days' => (int) $this->starts_on->diffInDays($this->ends_on) + 1,
            'reason' => $this->reason,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'decision_note' => $this->decision_note,
            'decided_by' => $this->relationLoaded('decider') ? $this->decider?->name : null,
            'requested_at' => $this->created_at?->setTimezone($timezone)->format('j M Y, H:i'),
            'staff_name' => $this->relationLoaded('user') ? $this->user->name : null,
            'staff_id' => $this->user_id,
        ];
    }
}
