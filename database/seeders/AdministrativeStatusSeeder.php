<?php

namespace Database\Seeders;

use App\Models\AdministrativeStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdministrativeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdministrativeStatus::create(['status' => 'Yangi', 'description' => 'agar inspector tekshirishga borganida object qurish boshlangan bolsa administrative yuborish statusi']);
        AdministrativeStatus::create(['status' => 'Ko’rib chiqilmoqda', 'description' => 'Administrative Qabul qilgan']);
        AdministrativeStatus::create(['status' => 'Sudga jo’natildi', 'description' => 'Administrative Sudiyaga yuborgan']);
        AdministrativeStatus::create(['status' => 'Chora ko’rilgan', 'description' => '']);
        AdministrativeStatus::create(['status' => 'Object boshlanmagan', 'description' => 'Inspektor tekshirganda object boshlanmagan']);
        AdministrativeStatus::create(['status' => 'Qurilish ishlari qisman boshlangan yoki yakunlangan', 'description' => 'agar inspector tekshirishga borganida object qurish boshlangan bolsa administrative yuborish statusi']);
        AdministrativeStatus::create(['status' => 'Qurilish ishlari boshlanmagan', 'description' => 'Qurilish ishlari boshlanmagan']);
        AdministrativeStatus::create(['status' => 'Ma\'muriy javobgarlikka tortilgan', 'description' => 'Ma\'muriy javobgarlikka tortilgan']);
    }
}
