<?php

namespace App\Models;

use App\Concerns\EncryptsAmounts;
use App\Enums\PayrollStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\PayrollRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One staff member's pay for one month. Admins only.
 *
 * Amounts are in kobo and encrypted at rest. The monthly salary is copied
 * from the staff member when the pay is approved, so a later pay rise does
 * not change past months. Approved pay is the salary plus the attendance
 * adjustment, which an admin enters (negative for a deduction).
 *
 * @property int $id
 * @property int $user_id
 * @property CarbonImmutable $month
 * @property int|null $monthly_salary
 * @property int|null $attendance_adjustment
 * @property string|null $adjustment_note
 * @property PayrollStatus $status
 * @property int|null $approved_by
 * @property CarbonImmutable|null $approved_at
 * @property int|null $paid_by
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['month', 'attendance_adjustment', 'adjustment_note'])]
class PayrollRecord extends Model
{
    /** @use HasFactory<PayrollRecordFactory> */
    use EncryptsAmounts, HasFactory;

    /**
     * A new record starts as Pending, before it is saved.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => PayrollStatus::Pending->value,
    ];

    /**
     * The amount to pay: the salary plus the attendance adjustment, or null
     * when no salary is known.
     */
    public function approvedPay(?int $currentSalary = null): ?int
    {
        $salary = $this->monthly_salary ?? $currentSalary;

        return $salary === null ? null : $salary + ($this->attendance_adjustment ?? 0);
    }

    /**
     * The record for one month (organisation time).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function forMonth(Builder $query, CarbonInterface $month): void
    {
        $query->whereDate('month', $month->format('Y-m-01'));
    }

    /**
     * The staff member being paid.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who approved the pay.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * The admin who marked the pay as paid.
     *
     * @return BelongsTo<User, $this>
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * The salary for the month, copied when the pay is approved.
     *
     * @return Attribute<int|null, int|null>
     */
    protected function monthlySalary(): Attribute
    {
        return self::encryptedAmount();
    }

    /**
     * The admin's adjustment for attendance: negative for a deduction.
     *
     * @return Attribute<int|null, int|null>
     */
    protected function attendanceAdjustment(): Attribute
    {
        return self::encryptedAmount();
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
            'status' => PayrollStatus::class,
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
