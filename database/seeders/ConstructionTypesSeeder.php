<?php

namespace Database\Seeders;

use App\Models\ConstructionTypes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConstructionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ConstructionTypes::create(['type'=>'Yangi', 'description'=>'Yangi']);
        ConstructionTypes::create(['type'=>'Rekonstruksiya', 'description'=>'Rekonstruksiya']);
        ConstructionTypes::create(['type'=>'Mukammal ta\'mirlash', 'description'=>'Mukammal ta\'mirlash']);
        ConstructionTypes::create(['type'=>'Modernizatsiya', 'description'=>'Modernizatsiya']);
        ConstructionTypes::create(['type'=>'Joriy tamirlash', 'description'=>'Joriy tamirlash']);
        ConstructionTypes::create(['type'=>'Jihozlash', 'description'=>'Jihozlash']);
        ConstructionTypes::create(['type'=>'Landshaft dizayni', 'description'=>'Landshaft dizayni']);
    }
}
