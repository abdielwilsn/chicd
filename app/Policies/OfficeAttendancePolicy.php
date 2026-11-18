<?php

namespace App\Policies;

use App\Models\OfficeAttendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OfficeAttendancePolicy
{
    /**
     * Determine whether the user can clock out of the session.
     *
     * Another person's session is reported as not found so staff cannot
     * discover which records exist.
     */
    public function update(User $user, OfficeAttendance $officeAttendance): Response
    {
        return $officeAttendance->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
