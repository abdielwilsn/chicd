<?php

namespace App\Enums;

/**
 * Where a staff member's pay for a month is. A month with no payroll record
 * yet is treated as Pending. Pay can only be approved once the month's
 * timesheet is approved, and only approved pay can be marked as paid.
 */
enum PayrollStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';

    /**
     * Human-readable label shown to admins.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Paid => 'Paid',
        };
    }
}
