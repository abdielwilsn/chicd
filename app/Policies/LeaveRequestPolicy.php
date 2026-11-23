<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveRequestPolicy
{
    /**
     * Determine whether the user can cancel the request.
     *
     * Another person's request is reported as not found so staff cannot
     * discover which requests exist.
     */
    public function cancel(User $user, LeaveRequest $leaveRequest): Response
    {
        return $leaveRequest->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
