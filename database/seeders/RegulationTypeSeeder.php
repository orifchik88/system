<?php

namespace Database\Seeders;

use App\Models\RegulationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegulationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $array = [
            'Davlat nazorati',
            'Qurilish qatnashchilari',
        ];

        foreach ($array as $value) {
            RegulationType::create(['type' => $value]);
        }
    }
}
