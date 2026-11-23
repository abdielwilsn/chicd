<?php

namespace App\Actions\Staff;

use App\Enums\UserRole;
use App\Mail\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Creates a staff portal account and emails the staff member a link to
 * choose their own password, so no one else ever knows it.
 *
 * Public registration is turned off: this is the only way accounts are made,
 * from the admin Staff accounts page or the staff:create command.
 */
class InviteStaffMember
{
    /**
     * Create the account and send the invitation. The email address counts as
     * verified because the admin vouches for it and the invitation can only be
     * used from that inbox.
     */
    public function handle(string $name, string $email, UserRole $role): User
    {
        $user = new User([
            'name' => $name,
            'email' => Str::lower($email),
            'password' => Hash::make(Str::password(40)),
        ]);
        $user->role = $role;
        $user->email_verified_at = now();
        $user->save();

        $this->sendInvitation($user);

        return $user;
    }

    /**
     * Email a link to set a password. Sending it again replaces the previous
     * link, which also lets an admin help someone locked out of their account.
     */
    public function sendInvitation(User $user): void
    {
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        Mail::to($user)->send(new StaffInvitation(
            $user,
            route('password.reset', ['token' => $token, 'email' => $user->email]),
        ));
    }
}
