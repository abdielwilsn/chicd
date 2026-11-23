<?php

namespace App\Actions\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Stops a staff member who has left from signing in, while keeping their
 * account, attendance records and timesheets.
 *
 * They are signed out everywhere straight away: their sessions are removed,
 * their "remember me" token is replaced and any unused password link stops
 * working. EnsureAccountIsActive also signs out a deactivated account on its
 * next request, whichever session driver is in use.
 */
class DeactivateStaffMember
{
    /**
     * @return int The number of office sessions and field activities the staff member left open, which an admin should close on their timesheet
     */
    public function handle(User $staffMember, User $admin): int
    {
        DB::transaction(function () use ($staffMember, $admin): void {
            $staffMember->forceFill([
                'deactivated_at' => now(),
                'deactivated_by' => $admin->id,
                'remember_token' => Str::random(60),
            ])->save();

            if (config('session.driver') === 'database') {
                DB::connection(config('session.connection'))
                    ->table(config('session.table', 'sessions'))
                    ->where('user_id', $staffMember->id)
                    ->delete();
            }
        });

        Password::broker(config('fortify.passwords'))->deleteToken($staffMember);

        return $staffMember->officeAttendances()->open()->count()
            + $staffMember->fieldActivities()->open()->count();
    }

    /**
     * Let a returning staff member sign in again. They keep their old
     * password; an admin can email them a new password link.
     */
    public function reactivate(User $staffMember): void
    {
        $staffMember->forceFill(['deactivated_at' => null, 'deactivated_by' => null])->save();
    }
}
