<?php
// database/seeders/VacuumStatusSeeder.php

namespace Database\Seeders;

use App\Models\VacuumStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VacuumStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default vacuum status record
        VacuumStatus::firstOrCreate(
            ['id' => 1], // Pastikan hanya ada 1 record
            [
                'state' => 'standby',
                'power_mode' => 'normal',
                'power_value' => 200
            ]
        );

        echo "✓ Default Vacuum Status created: power_mode=normal, power_value=200\n";
    }
}