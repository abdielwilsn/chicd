<?php

namespace App\Http\Resources;

use App\Actions\Timesheets\BuildMonthlyTimesheet;
use App\Models\OfficeAttendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An office session as shown to an admin: the staff view plus the location
 * captured at clock-in and clock-out, and the values for the edit form.
 *
 * @mixin OfficeAttendance
 */
class AdminOfficeAttendanceResource extends JsonResource
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
            ...OfficeAttendanceResource::make($this->resource)->resolve(),
            'flagged' => BuildMonthlyTimesheet::isFlagged($this->resource),
            'clock_in_location' => [
                'latitude' => $this->clock_in_latitude,
                'longitude' => $this->clock_in_longitude,
                'accuracy_metres' => $this->clock_in_accuracy_metres,
                'distance_metres' => $this->clock_in_distance_metres,
            ],
            'clock_out_location' => $this->clock_out_latitude === null ? null : [
                'latitude' => $this->clock_out_latitude,
                'longitude' => $this->clock_out_longitude,
                'accuracy_metres' => $this->clock_out_accuracy_metres,
                'distance_metres' => $this->clock_out_distance_metres,
            ],
            'edit' => [
                'clocked_in_at' => $this->clocked_in_at->setTimezone($timezone)->format('Y-m-d\TH:i'),
                'clocked_out_at' => $this->clocked_out_at?->setTimezone($timezone)->format('Y-m-d\TH:i'),
            ],
        ];
    }
}
