<?php

namespace Database\Seeders;

use App\Models\LevelStatus;
use Illuminate\Database\Seeder;

class LevelStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LevelStatus::create([
            'name' => 'Ishlar boshlanmagan',
        ]);

        LevelStatus::create([
            'name' => 'Qurilish jarayonida',
        ]);

        LevelStatus::create([
            'name' => 'Ishlar yakunlangan',
        ]);

    }
}
