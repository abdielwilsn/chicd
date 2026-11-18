<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Seed the CHICD office used for geofenced clock-ins.
     *
     * The coordinates are a placeholder for central Uyo. Replace them with
     * the surveyed office location before staff start using the portal.
     */
    public function run(): void
    {
        Office::firstOrCreate(
            ['name' => 'Uyo office'],
            ['latitude' => 5.0377, 'longitude' => 7.9128, 'geofence_radius_metres' => 100],
        );
    }
}
