<?php

namespace App\Http\Resources;

use App\Models\FieldActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A field activity as shown to an admin: the staff view plus the location
 * captured at the start and end, and the values for the edit form.
 *
 * @mixin FieldActivity
 */
class AdminFieldActivityResource extends JsonResource
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
            ...FieldActivityResource::make($this->resource)->resolve(),
            'start_location' => [
                'latitude' => $this->start_latitude,
                'longitude' => $this->start_longitude,
                'accuracy_metres' => $this->start_accuracy_metres,
                'distance_metres' => null,
            ],
            'end_location' => $this->end_latitude === null ? null : [
                'latitude' => $this->end_latitude,
                'longitude' => $this->end_longitude,
                'accuracy_metres' => $this->end_accuracy_metres,
                'distance_metres' => null,
            ],
            'edit' => [
                'started_at' => $this->started_at->setTimezone($timezone)->format('Y-m-d\TH:i'),
                'ended_at' => $this->ended_at?->setTimezone($timezone)->format('Y-m-d\TH:i'),
                'programme' => $this->programme,
                'activity' => $this->activity,
                'location' => $this->location,
                'description' => $this->description,
            ],
        ];
    }
}
