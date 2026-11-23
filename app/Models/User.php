<?php

namespace App\Models;

use App\Concerns\EncryptsAmounts;
use App\Enums\UserRole;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property int|null $monthly_salary Kobo, encrypted at rest
 * @property CarbonImmutable|null $deactivated_at
 * @property int|null $deactivated_by
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'monthly_salary'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use EncryptsAmounts, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Whether the user has the Admin level of access.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Whether the person can sign in. A staff member who leaves is
     * deactivated rather than deleted, so their attendance records and
     * timesheets are kept.
     */
    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    /**
     * Current monthly salary in kobo, encrypted at rest and hidden from
     * serialization so it never reaches a staff member's browser. Admins
     * only.
     *
     * @return Attribute<int|null, int|null>
     */
    protected function monthlySalary(): Attribute
    {
        return self::encryptedAmount();
    }

    /**
     * Staff whose records belong in a month: everyone still active, and
     * anyone who left during or after that month.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function employedDuring(Builder $query, CarbonInterface $month): void
    {
        $query->where(fn (Builder $query) => $query->whereNull('deactivated_at')->orWhere('deactivated_at', '>=', $month->utc()));
    }

    /**
     * The admin who deactivated the account.
     *
     * @return BelongsTo<self, $this>
     */
    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'deactivated_by');
    }

    /**
     * Office sessions recorded by the user.
     *
     * @return HasMany<OfficeAttendance, $this>
     */
    public function officeAttendances(): HasMany
    {
        return $this->hasMany(OfficeAttendance::class);
    }

    /**
     * Field activities recorded by the user.
     *
     * @return HasMany<FieldActivity, $this>
     */
    public function fieldActivities(): HasMany
    {
        return $this->hasMany(FieldActivity::class);
    }

    /**
     * The user's monthly timesheets that have been reviewed.
     *
     * @return HasMany<Timesheet, $this>
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Requests the user has made to correct their attendance.
     *
     * @return HasMany<CorrectionRequest, $this>
     */
    public function correctionRequests(): HasMany
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    /**
     * Leave the user has asked for.
     *
     * @return HasMany<LeaveRequest, $this>
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * The user's monthly pay records. Admins only.
     *
     * @return HasMany<PayrollRecord, $this>
     */
    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => UserRole::class,
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
