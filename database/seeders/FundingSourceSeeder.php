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
        FundingSource::query()->truncate();
        FundingSource::create(['name' => 'Davlat', 'description' => 'Davlat budjeti va mahalliy budjet']);
        FundingSource::create(['name' => 'Buyurtmachi', 'description' => 'Buyurtmachining o\'z mablag\'lari hisobidan']);
        FundingSource::create(['name' => 'Tadbirkorlik', 'description' => 'Xalqaro moliya institutlari']);
    }
}



