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
        ActStatus::query()->truncate();
        $array = [
            'Chora tadbir taqdim etildi',
            'Chora tadbir ma\'qullandi',
            'Chora tadbir rad etildi',
            'Dalolatnoma taqdim etildi',
            'Dalolatnoma ma\'qullandi',
            'Dalolatnoma rad etildi',
            'Dalolatnoma taqdim etildi(SRM)',
            'Dalolatnoma ma\'qullandi(SMR)',
            'Dalolatnoma rad etildi(SMR)',
            'Muddat uzaytirish so\'raldi',
            'Muddat uzaytirish ma\'qullandi',
            'Muddat uzaytirish rad etildi',
            'Bartaraf etildi',
        ];

        foreach ($array as $value) {
            ActStatus::create(['status' => $value]);
        }
    }
}
