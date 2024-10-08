<?php

namespace Database\Seeders;

use App\Models\LawyerStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LawyerStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LawyerStatus::create(['name' => 'Yangi']);
        LawyerStatus::create(['name' => 'Jarayonda']);
        LawyerStatus::create(['name' => 'Ma\'muriy qilindi']);
        LawyerStatus::create(['name' => 'Demontaj qilindi']);
    }
}
