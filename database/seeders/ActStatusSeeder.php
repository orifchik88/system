<?php

namespace Database\Seeders;

use App\Models\ActStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $array = [
            'Chora tadbir taqdim etildi',
            'Chora tadbir ma\'qullandi',
            'Chora tadbir rad etildi',
            'Dalolatnoma taqdim etildi',
            'Dalolatnoma ma\'qullandi',
            'Dalolatnoma rad etildi',
            'Muddat uzaytirish so\'raldi',
            'Muddat uzaytirish ma\'qullandi',
            'Muddat uzaytirish rad etildi',
            'Dalolatnoma ko\'rib chiqildi',
        ];

        foreach ($array as $value) {
            ActStatus::create(['status' => $value]);
        }
    }
}
