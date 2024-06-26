<?php

namespace Database\Seeders;

use App\Models\ResponseType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResponseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ResponseType::create(['name' => 'Davlat', 'description' => 'Davlat tomonidan moliyalashtirilgan']);
        ResponseType::create(['name' => 'Tadbirkorlik', 'description' => 'Xususiy  tashkilot tomonidan moliyalashtirilgan']);
    }
}
