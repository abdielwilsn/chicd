<?php

namespace App\Policies;

use App\Models\FieldActivity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FieldActivityPolicy
{
    /**
     * Determine whether the user can end the activity.
     *
     * Another person's activity is reported as not found so staff cannot
     * discover which records exist.
     */
    public function update(User $user, FieldActivity $fieldActivity): Response
    {
        return $fieldActivity->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
