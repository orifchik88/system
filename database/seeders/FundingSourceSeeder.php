<?php

namespace Database\Seeders;

use App\Models\FundingSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FundingSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FundingSource::create(['name' => 'Davlat', 'description' => 'Davlat tomonidan moliyalashtirilgan']);
        FundingSource::create(['name' => 'Tadbirkorlik', 'description' => 'Xususiy  tashkilot tomonidan moliyalashtirilgan']);
    }
}
