<?php

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Seed sample publications so the reports pages can be previewed locally.
     */
    public function run(): void
    {
        Publication::factory()->annualReport()->featured()->create(['year' => 2026]);
        Publication::factory()->governance()->create(['title' => 'Safeguarding Policy', 'year' => 2026]);
        Publication::factory()->governance()->create(['title' => 'Privacy and Data Protection Notice', 'year' => 2026]);
        Publication::factory()->count(4)->create();
    }
}
