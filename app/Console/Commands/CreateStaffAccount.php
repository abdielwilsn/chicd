<?php

namespace App\Console\Commands;

use App\Actions\Staff\InviteStaffMember;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Creates a staff portal account from the command line and emails a link to
 * set the password. Use it to create the first admin on a new server; admins
 * then add everyone else from the Staff accounts page.
 */
#[Signature('staff:create {email : The staff member\'s email address, used to sign in} {name : Their full name} {--admin : Give the account admin permissions}')]
#[Description('Create a staff portal account and email a link to set the password')]
class CreateStaffAccount extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(InviteStaffMember $inviteStaffMember): int
    {
        $validator = Validator::make(
            ['email' => $this->argument('email'), 'name' => $this->argument('name')],
            ['email' => ['required', 'email', 'max:255', 'unique:'.User::class.',email'], 'name' => ['required', 'string', 'max:120']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $role = $this->option('admin') ? UserRole::Admin : UserRole::Staff;
        $user = $inviteStaffMember->handle($this->argument('name'), $this->argument('email'), $role);

        $this->components->info(sprintf('%s account created for %s. A link to set the password was emailed to %s.', $role->label(), $user->name, $user->email));

        return self::SUCCESS;
    }
}
