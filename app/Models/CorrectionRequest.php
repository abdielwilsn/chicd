<?php

namespace App\Models;

use App\Enums\CorrectionStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\CorrectionRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A staff member's request to correct an error in their attendance: either
 * about a specific office session or field activity, or about a day with a
 * record missing altogether.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $correctable_type
 * @property int|null $correctable_id
 * @property CarbonImmutable $work_date
 * @property string $details
 * @property CorrectionStatus $status
 * @property int|null $resolved_by
 * @property CarbonImmutable|null $resolved_at
 * @property string|null $resolution_note
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['work_date', 'details', 'status', 'resolved_by', 'resolved_at', 'resolution_note'])]
class CorrectionRequest extends Model
{
    /** @use HasFactory<CorrectionRequestFactory> */
    use HasFactory;

    /**
     * Whether an admin has not dealt with the request yet.
     */
    public function isPending(): bool
    {
        return $this->status === CorrectionStatus::Pending;
    }

    /**
     * Close the request with an outcome and a note for the staff member.
     */
    public function close(CorrectionStatus $status, User $admin, string $note): void
    {
        $this->update([
            'status' => $status,
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
            'resolution_note' => $note,
        ]);
    }

    /**
     * Requests an admin has not dealt with yet.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', CorrectionStatus::Pending);
    }

    /**
     * Requests about a day in the month starting at the given moment
     * (organisation time).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function forMonth(Builder $query, CarbonInterface $month): void
    {
        $query->whereDate('work_date', '>=', $month->format('Y-m-01'))->whereDate('work_date', '<=', $month->endOfMonth()->format('Y-m-d'));
    }

    /**
     * The staff member who asked for the correction.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The office session or field activity the request is about, if any.
     *
     * @return MorphTo<Model, $this>
     */
    public function correctable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The admin who dealt with the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'status' => CorrectionStatus::class,
            'resolved_at' => 'datetime',
        ];
    }
}
