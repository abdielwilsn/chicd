<?php

namespace App\Enums;

/**
 * The outcome of a staff member's request to correct an attendance record.
 */
enum CorrectionStatus: string
{
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Declined = 'declined';

    /**
     * Human-readable label shown in the staff portal.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Waiting for an admin',
            self::Resolved => 'Resolved',
            self::Declined => 'Declined',
        };
    }
}
