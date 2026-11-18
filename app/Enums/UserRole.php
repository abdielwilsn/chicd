<?php

namespace App\Enums;

/**
 * The two signed-in levels named in the brief. Admins see all staff
 * attendance, approve timesheets and manage payroll.
 */
enum UserRole: string
{
    case Staff = 'staff';
    case Admin = 'admin';

    /**
     * Human-readable label shown in the staff portal.
     */
    public function label(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Admin => 'Admin',
        };
    }
}
