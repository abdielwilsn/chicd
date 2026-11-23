<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([OfficeSeeder::class]);

        // Test accounts with the password "password" must never exist on a live
        // server. Create real accounts with `php artisan staff:create`.
        if (app()->isLocal()) {
            $this->call([StaffSeeder::class, PublicationSeeder::class, WebsiteContentSeeder::class]);
        }
    }
}
