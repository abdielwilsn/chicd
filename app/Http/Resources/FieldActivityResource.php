<?php

namespace App\Http\Resources;

use App\Models\FieldActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A field activity as shown to the staff member who recorded it. Times are
 * formatted in the organisation timezone. Coordinates are not sent.
 *
 * @mixin FieldActivity
 */
class FieldActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, date: string, programme: string, activity: string, description: string|null, location: string, started_time: string, ended_time: string|null, minutes: int|null}
     */
    public function toArray(Request $request): array
    {
        $timezone = config('chicd.timezone');

        return [
            'id' => $this->id,
            'date' => $this->started_at->setTimezone($timezone)->format('D j M Y'),
            'programme' => $this->programme,
            'activity' => $this->activity,
            'description' => $this->description,
            'location' => $this->location,
            'started_time' => $this->started_at->setTimezone($timezone)->format('H:i'),
            'ended_time' => $this->ended_at?->setTimezone($timezone)->format('H:i'),
            'minutes' => $this->ended_at ? (int) $this->started_at->diffInMinutes($this->ended_at) : null,
        ];
    }
}
