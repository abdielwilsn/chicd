<?php

namespace App\Http\Resources;

use App\Models\OfficeAttendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An office session as shown to the staff member who recorded it. Times are
 * formatted in the organisation timezone. Coordinates are not sent.
 *
 * @mixin OfficeAttendance
 */
class OfficeAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, date: string, office: string|null, clocked_in_time: string, clocked_in_within_geofence: bool, clocked_out_time: string|null, clocked_out_within_geofence: bool|null, minutes: int|null}
     */
    public function toArray(Request $request): array
    {
        $timezone = config('chicd.timezone');

        return [
            'id' => $this->id,
            'date' => $this->clocked_in_at->setTimezone($timezone)->format('D j M Y'),
            'office' => $this->office?->name,
            'clocked_in_time' => $this->clocked_in_at->setTimezone($timezone)->format('H:i'),
            'clocked_in_within_geofence' => $this->clocked_in_within_geofence,
            'clocked_out_time' => $this->clocked_out_at?->setTimezone($timezone)->format('H:i'),
            'clocked_out_within_geofence' => $this->clocked_out_within_geofence,
            'minutes' => $this->clocked_out_at ? (int) $this->clocked_in_at->diffInMinutes($this->clocked_out_at) : null,
        ];
    }
}
