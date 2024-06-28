<?php

namespace Database\Seeders;

use App\Models\DifficultyCategory;
use Illuminate\Database\Seeder;

class DifficultyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DifficultyCategory::create(['difficulty' => 'I', 'description' => 'I']);
        DifficultyCategory::create(['difficulty' => 'II', 'description' => 'II']);
        DifficultyCategory::create(['difficulty' => 'III', 'description' => 'III']);
        DifficultyCategory::create(['difficulty' => 'IV', 'description' => 'IV']);
    }
}
