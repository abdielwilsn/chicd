<?php

namespace App\Models;

use App\Concerns\HasRecordChanges;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\OfficeAttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * One office working session, from Clock In to Clock Out.
 *
 * Location is captured only at the two moments the staff member presses a
 * button. A point outside the geofence is recorded and flagged, not refused.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $office_id
 * @property CarbonImmutable $clocked_in_at
 * @property float $clock_in_latitude
 * @property float $clock_in_longitude
 * @property int|null $clock_in_accuracy_metres
 * @property int|null $clock_in_distance_metres
 * @property bool $clocked_in_within_geofence
 * @property CarbonImmutable|null $clocked_out_at
 * @property float|null $clock_out_latitude
 * @property float|null $clock_out_longitude
 * @property int|null $clock_out_accuracy_metres
 * @property int|null $clock_out_distance_metres
 * @property bool|null $clocked_out_within_geofence
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable([
    'office_id',
    'clocked_in_at',
    'clock_in_latitude',
    'clock_in_longitude',
    'clock_in_accuracy_metres',
    'clock_in_distance_metres',
    'clocked_in_within_geofence',
    'clocked_out_at',
    'clock_out_latitude',
    'clock_out_longitude',
    'clock_out_accuracy_metres',
    'clock_out_distance_metres',
    'clocked_out_within_geofence',
])]
class OfficeAttendance extends Model
{
    /** @use HasFactory<OfficeAttendanceFactory> */
    use HasFactory, HasRecordChanges;

    /**
     * Whether the staff member has not clocked out yet.
     */
    public function isOpen(): bool
    {
        return $this->clocked_out_at === null;
    }

    /**
     * Sessions that have not been clocked out.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereNull('clocked_out_at');
    }

    /**
     * Sessions that started between two moments (inclusive start, exclusive end).
     * The moments may be in any timezone; they are compared in storage time.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function startedBetween(Builder $query, CarbonInterface $from, CarbonInterface $until): void
    {
        $storageTimezone = config('app.timezone');

        $query->where('clocked_in_at', '>=', $from->setTimezone($storageTimezone))
            ->where('clocked_in_at', '<', $until->setTimezone($storageTimezone));
    }

    /**
     * The staff member who recorded the session.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The office whose geofence the session was checked against.
     *
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Staff requests to correct this record.
     *
     * @return MorphMany<CorrectionRequest, $this>
     */
    public function correctionRequests(): MorphMany
    {
        return $this->morphMany(CorrectionRequest::class, 'correctable');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'clocked_in_at' => 'datetime',
            'clock_in_latitude' => 'float',
            'clock_in_longitude' => 'float',
            'clocked_in_within_geofence' => 'boolean',
            'clocked_out_at' => 'datetime',
            'clock_out_latitude' => 'float',
            'clock_out_longitude' => 'float',
            'clocked_out_within_geofence' => 'boolean',
        ];
    }
}
