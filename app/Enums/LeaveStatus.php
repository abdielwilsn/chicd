<?php

namespace App\Enums;

/**
 * Where a leave request is. Only approved leave stops working days counting
 * as absences.
 */
enum LeaveStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Cancelled = 'cancelled';

    /**
     * Human-readable label shown in the staff portal.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Waiting for approval',
            self::Approved => 'Approved',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
        };
    }
}
