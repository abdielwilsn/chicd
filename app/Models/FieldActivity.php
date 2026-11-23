<?php

namespace App\Models;

use App\Concerns\HasRecordChanges;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\FieldActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A field activity recorded in Field/Activity mode.
 *
 * Location is captured only when the activity starts and when it ends.
 *
 * @property int $id
 * @property int $user_id
 * @property string $programme
 * @property string $activity
 * @property string|null $description
 * @property string $location
 * @property CarbonImmutable $started_at
 * @property float $start_latitude
 * @property float $start_longitude
 * @property int|null $start_accuracy_metres
 * @property CarbonImmutable|null $ended_at
 * @property float|null $end_latitude
 * @property float|null $end_longitude
 * @property int|null $end_accuracy_metres
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable([
    'programme',
    'activity',
    'description',
    'location',
    'started_at',
    'start_latitude',
    'start_longitude',
    'start_accuracy_metres',
    'ended_at',
    'end_latitude',
    'end_longitude',
    'end_accuracy_metres',
])]
class FieldActivity extends Model
{
    /** @use HasFactory<FieldActivityFactory> */
    use HasFactory, HasRecordChanges;

    /**
     * Whether the activity has not been ended yet.
     */
    public function isOpen(): bool
    {
        return $this->ended_at === null;
    }

    /**
     * Activities that have not been ended.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereNull('ended_at');
    }

    /**
     * Activities that started between two moments (inclusive start, exclusive end).
     * The moments may be in any timezone; they are compared in storage time.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function startedBetween(Builder $query, CarbonInterface $from, CarbonInterface $until): void
    {
        $storageTimezone = config('app.timezone');

        $query->where('started_at', '>=', $from->setTimezone($storageTimezone))
            ->where('started_at', '<', $until->setTimezone($storageTimezone));
    }

    /**
     * The staff member who recorded the activity.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            'started_at' => 'datetime',
            'start_latitude' => 'float',
            'start_longitude' => 'float',
            'ended_at' => 'datetime',
            'end_latitude' => 'float',
            'end_longitude' => 'float',
        ];
    }
}
