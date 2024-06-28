<?php

namespace Database\Seeders;

use App\Models\ObjectStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ObjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ObjectStatus::create(['name'=> 'Yangi',  'description' => 'Obyektda qurilish boshlanmagan']);
        ObjectStatus::create(['name'=> 'Jarayonda', 'description' => 'Qurilish jarayonda']);
        ObjectStatus::create(['name'=> 'Muzlatilgan', 'description' => 'Obyektda qurilish qurilish qatnashuvchilari tomonidan to\'xtatilgan']);
        ObjectStatus::create(['name'=> 'To\'xtatilgan', 'description' => 'Obyektda qurilish GASN xodimlari tomonidan to\'xtatilgan']);
        ObjectStatus::create(['name'=> 'Topshirilgan', 'description' => 'Obyetkda qurilish tugatilgan va foydalanishga tayyor']);
    }
}
