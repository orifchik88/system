<?php

namespace Database\Seeders;

use App\Enums\ObjectTypeEnum;
use App\Models\WorkType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Qurilish maydonining tayyorgarligi',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Kotlovan',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Fundament va (yoki) yerto\'la',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Konstruksiya elementlari',
            'is_multiple_floor' => true

        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Tom qismi',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Tashqi pardoz ishlari',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Ichki pardoz ishlari',
            'is_multiple_floor' => true
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Mexanik ishlar - kommunikatsiya tarmoqlari',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Elektr ishlari',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'name' => 'Zaif oqim tizimlari',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'name' => 'Qurilish maydonining tayyorgarligi.',
        ]);


        WorkType::create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'name' => 'Umimiy cheklist',
        ]);

        WorkType::create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'name' => 'Doimiy cheklist',
            'status' => false
        ]);

    }
}
