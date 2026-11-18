<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Seed staff portal accounts for local testing. Every account uses the
     * password "password". Safe to run more than once.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Test Admin', 'email' => 'admin@example.com', 'role' => UserRole::Admin],
            ['name' => 'Test User', 'email' => 'test@example.com', 'role' => UserRole::Staff],
            ['name' => 'Ada Okon', 'email' => 'ada@example.com', 'role' => UserRole::Staff],
            ['name' => 'Emem Udoh', 'email' => 'emem@example.com', 'role' => UserRole::Staff],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrNew(['email' => $account['email']]);

            if (! $user->exists) {
                $user->fill(User::factory()->raw(['name' => $account['name'], 'email' => $account['email']]));
            }

            $user->role = $account['role'];
            $user->save();
        }
    }
}
