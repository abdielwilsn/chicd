<?php

namespace App\Enums;

/**
 * The kinds of leave a staff member can request.
 */
enum LeaveType: string
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Compassionate = 'compassionate';
    case Other = 'other';

    /**
     * Human-readable label shown in the staff portal.
     */
    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Annual leave',
            self::Sick => 'Sick leave',
            self::Compassionate => 'Compassionate leave',
            self::Other => 'Other leave',
        };
    }
}
