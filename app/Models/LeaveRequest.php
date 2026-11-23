<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\LeaveRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Leave a staff member asked for. Once an admin approves it, the working
 * days it covers count as leave rather than absence.
 *
 * @property int $id
 * @property int $user_id
 * @property LeaveType $type
 * @property CarbonImmutable $starts_on
 * @property CarbonImmutable $ends_on
 * @property string|null $reason
 * @property LeaveStatus $status
 * @property int|null $decided_by
 * @property CarbonImmutable|null $decided_at
 * @property string|null $decision_note
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['type', 'starts_on', 'ends_on', 'reason'])]
class LeaveRequest extends Model
{
    /** @use HasFactory<LeaveRequestFactory> */
    use HasFactory;

    /**
     * A new request waits for a decision, before it is saved.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => LeaveStatus::Pending->value,
    ];

    /**
     * Whether an admin has not decided on the request yet.
     */
    public function isPending(): bool
    {
        return $this->status === LeaveStatus::Pending;
    }

    /**
     * Record an admin's decision.
     */
    public function decide(LeaveStatus $status, User $admin, ?string $note): void
    {
        $this->forceFill([
            'status' => $status,
            'decided_by' => $admin->id,
            'decided_at' => now(),
            'decision_note' => $note,
        ])->save();
    }

    /**
     * Requests that are waiting for a decision or approved, which a new
     * request must not overlap.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereIn('status', [LeaveStatus::Pending, LeaveStatus::Approved]);
    }

    /**
     * Approved leave.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', LeaveStatus::Approved);
    }

    /**
     * Requests covering any day from the first to the last date given.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function overlapping(Builder $query, CarbonInterface $from, CarbonInterface $to): void
    {
        $query->whereDate('starts_on', '<=', $to->toDateString())->whereDate('ends_on', '>=', $from->toDateString());
    }

    /**
     * The staff member who asked for leave.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who approved or declined the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LeaveType::class,
            'status' => LeaveStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'decided_at' => 'datetime',
        ];
    }
}
