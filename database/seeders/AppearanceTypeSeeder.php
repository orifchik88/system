<?php

namespace Database\Seeders;

use App\Models\AppearanceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppearanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppearanceType::create(['type' => 'Noturar', 'description' => 'Noturar']);
        AppearanceType::create(['type' => 'Turar joy', 'description' => 'Turar joy']);
    }
}
