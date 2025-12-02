<?php
// database/seeders/DayahisapSeeder.php

namespace Database\Seeders;

use App\Models\Dayahisap;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DayahisapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial dayahisap record with NORMAL mode
        Dayahisap::create([
            'value' => 200,
            'mode' => 'normal'
        ]);

        echo "✓ Initial Dayahisap created: value=200, mode=normal\n";
    }
}