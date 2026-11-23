<?php

namespace App\Enums;

/**
 * Where a staff member's monthly timesheet is in review. A month with no
 * timesheet row yet is treated as Pending.
 */
enum TimesheetStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';

    /**
     * Human-readable label shown in the staff portal.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting approval',
            self::Approved => 'Approved',
        };
    }
}
