<?php

namespace App\Http\Resources;

use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A correction request as shown in the staff portal. Load the correctable
 * relation first; for admin lists, also load user and resolver.
 *
 * @mixin CorrectionRequest
 */
class CorrectionRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, work_date: string, work_date_label: string, record: string, record_type: string|null, record_id: int|null, details: string, status: string, status_label: string, resolution_note: string|null, resolved_by: string|null, requested_at: string, staff_name: string|null, staff_id: int}
     */
    public function toArray(Request $request): array
    {
        $timezone = config('chicd.timezone');

        return [
            'id' => $this->id,
            'work_date' => $this->work_date->toDateString(),
            'work_date_label' => $this->work_date->format('D j M Y'),
            'record' => $this->recordSummary(),
            'record_type' => $this->correctable_type,
            'record_id' => $this->correctable_id,
            'details' => $this->details,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'resolution_note' => $this->resolution_note,
            'resolved_by' => $this->relationLoaded('resolver') ? $this->resolver?->name : null,
            'requested_at' => $this->created_at->setTimezone($timezone)->format('j M Y, H:i'),
            'staff_name' => $this->relationLoaded('user') ? $this->user->name : null,
            'staff_id' => $this->user_id,
        ];
    }

    /**
     * A one-line description of the record the request is about.
     */
    private function recordSummary(): string
    {
        $timezone = config('chicd.timezone');
        $record = $this->correctable;

        return match (true) {
            $record instanceof OfficeAttendance => sprintf(
                'Office session from %s to %s',
                $record->clocked_in_at->setTimezone($timezone)->format('H:i'),
                $record->clocked_out_at?->setTimezone($timezone)->format('H:i') ?? 'no clock-out',
            ),
            $record instanceof FieldActivity => sprintf(
                'Field activity "%s" from %s to %s',
                $record->activity,
                $record->started_at->setTimezone($timezone)->format('H:i'),
                $record->ended_at?->setTimezone($timezone)->format('H:i') ?? 'not ended',
            ),
            $this->correctable_type !== null => 'A record that no longer exists',
            default => 'A missing or unrecorded day',
        };
    }
}
