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
        RegulationStatus::query()->truncate();
        $array = [
            'Chora tadbir taqdim qilish',
            'Chora tadbir tasdiqlash',
            'Dalolatnoma biriktirish',
            'Dalolatnoma tasdiqlash(inspektor)',
            'Dalolatnoma tasdiqlash(SMR)',
            'Bartaraf etildi',
            'Yuristda',
            'Kechiktirib ijrosi taminlandi'
        ];

        foreach ($array as $value) {
            RegulationStatus::create(['status' => $value]);
        }
    }
}
