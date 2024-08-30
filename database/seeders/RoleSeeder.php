<?php

namespace Database\Seeders;

use App\Enums\RoleTypeEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->truncate();
        $meta = [
            [
                'name' => 'Kadr',
                'description' => 'Respublika Kadri',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'Kadr',
                'description' => 'Viloyat Kadri',
                'type' => RoleTypeEnum::REGION
            ],

            [
                'name' => 'Inspector',
                'description' => 'Inspector',
                'type' => RoleTypeEnum::DISTRICT
            ],
            [
                'name' => 'Registrator',
                'description' => 'Registrator',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Ichki nazorat',
                'description' => 'Ichki nazorat',
                'type' => null

            ],
            [
                'name' => 'Texnik nazorat',
                'description' => 'Texnik nazorat',
                'type' => null

            ],
            [
                'name' => 'Muallif nazorati',
                'description' => 'Muallif nazorati',
                'type' => null
            ],
            [
                'name' => 'Buyurtmachi',
                'description' => 'Buyurtmachi',
                'type' => null
            ],

            [
                'name' => 'Loyihachi',
                'description' => 'Loyihachi yuridik Shaxs',
                'type' => null
            ],
            [
                'name' => 'Quruvchi',
                'description' => 'Quruvchi',
                'type' => null
            ],
            [
                'name' => 'Operator',
                'description' => 'Operator',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Boshliq',
                'description' => 'Boshliq',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Kuzatuvchi',
                'description' => 'Viloyat Kuzatuvchi',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Kuzatuvchi',
                'description' => 'Respublika Kuzatuvchi',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'O\'t O\'chirish',
                'description' => 'O\'t O\'chirish',
                'type' => RoleTypeEnum::DISTRICT
            ],
            [
                'name' => 'SES',
                'description' => 'SES',
                'type' => RoleTypeEnum::DISTRICT
            ],
            [
                'name' => 'Nogironlar jamiyati',
                'description' => 'Nogironlar jamiyati',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Nogironlar assotsatsiyasi',
                'description' => 'Nogironlar assotsatsiyasi',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Uy-joy inspeksiyasi',
                'description' => 'Uy-joy inspeksiyasi',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'SES Kadr',
                'description' => 'SES Kadr Respublika',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'SES Kadr',
                'description' => 'SES Kadr Viloyat',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'MCHS  Kadr',
                'description' => 'MCHS  Kadr Respublika',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'MCHS  Kadr',
                'description' => 'MCHS  Kadr Viloyat',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Nogironlar Jamiyati Kadr',
                'description' => 'Respublika Nogironlar Jamiyati Kadr',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'Nogironlar Assotsatsiya Kadr',
                'description' => 'Respublika Nogironlar Assotsatsiya Kadr',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'Uy-Joy Inspeksiya Kadr',
                'description' => 'Respublika Uy-Joy Inspeksiya Kadr',
                'type' => RoleTypeEnum::REPUBLIC
            ],
        ];


        foreach ($meta as $role) {
            Role::query()->create([
                'name' => $role['name'],
                'description' => $role['description'],
                'type' => $role['type'],
            ]);
        }
    }
}
