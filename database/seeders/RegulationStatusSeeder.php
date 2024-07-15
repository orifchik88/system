<?php

namespace Database\Seeders;

use App\Models\RegulationStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegulationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $array = [
            'Yangi',
            'Jarayonda',
            'Tasdiqlashda',
            'Tasdiqlandi',
            'Yopilgan',
            'Bartaraf etildi',
            'Chora tadbir taqdim etildi',
            'Dalolatnoma taqdim etildi',
            'Muddat uzaytirish so\'raldi',
            'Yopilmagan',
            'Rad etildi',
        ];

        foreach ($array as $value) {
            RegulationStatus::create(['status' => $value]);
        }
    }
}
