<?php

namespace App\Models;

use App\Concerns\HasRecordChanges;
use App\Enums\TimesheetStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\TimesheetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The review state of one staff member's month. The hours themselves are not
 * stored: they are worked out from the office sessions and field activities,
 * which cannot change while the month is approved.
 *
 * @property int $id
 * @property int $user_id
 * @property CarbonImmutable $month
 * @property TimesheetStatus $status
 * @property int|null $approved_by
 * @property CarbonImmutable|null $approved_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['month', 'status', 'approved_by', 'approved_at'])]
class Timesheet extends Model
{
    /** @use HasFactory<TimesheetFactory> */
    use HasFactory, HasRecordChanges;

    /**
     * Whether an admin has approved the month.
     */
    public function isApproved(): bool
    {
        return $this->status === TimesheetStatus::Approved;
    }

    /**
     * Whether any of the user's timesheets from the month containing the first
     * date to the month containing the last date is approved.
     */
    public static function anyApprovedBetween(User $user, CarbonInterface $from, CarbonInterface $to): bool
    {
        return static::query()
            ->whereBelongsTo($user)
            ->where('status', TimesheetStatus::Approved)
            ->whereDate('month', '>=', $from->format('Y-m-01'))
            ->whereDate('month', '<=', $to->format('Y-m-01'))
            ->exists();
    }

    /**
     * Whether the user's timesheet for the month containing the given moment
     * is approved, which locks that month's records against changes.
     */
    public static function isApprovedFor(User $user, CarbonInterface $moment): bool
    {
        $month = CarbonImmutable::instance($moment)->setTimezone(config('chicd.timezone'))->startOfMonth();

        return static::query()
            ->whereBelongsTo($user)
            ->forMonth($month)
            ->where('status', TimesheetStatus::Approved)
            ->exists();
    }

    /**
     * Timesheets for the month starting at the given moment (organisation time).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function forMonth(Builder $query, CarbonInterface $month): void
    {
        $query->whereDate('month', $month->format('Y-m-01'));
    }

    /**
     * The staff member the timesheet belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who approved the timesheet.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'month' => 'date',
            'status' => TimesheetStatus::class,
            'approved_at' => 'datetime',
        ];
    }
}
