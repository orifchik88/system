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
                'name' => 'Inspektor',
                'description' => 'Inspektor',
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
                'name' => 'Loyiha tashkiloti',
                'description' => 'Loyihachi yuridik Shaxs',
                'type' => null
            ],
            [
                'name' => 'Qurilish tashkiloti',
                'description' => 'Quruvchi yuridik Shaxs',
                'type' => null
            ],
            [
                'name' => 'Operator',
                'description' => 'Operator',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Inspeksiya boshlig\'i',
                'description' => 'Inspeksiya boshlig\'i',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Hududiy kuzatuvchi',
                'description' => 'Viloyat Kuzatuvchi',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'Respublika kuzatuvchi',
                'description' => 'Respublika Kuzatuvchi',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'Tuman FVB',
                'description' => 'O\'t O\'chirish tuman',
                'type' => RoleTypeEnum::DISTRICT
            ],
            [
                'name' => 'Tuman SEOM',
                'description' => 'SES tuman',
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
                'name' => 'SEOM Respublika kadr',
                'description' => 'SES Kadr Respublika',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'SEOM viloyat kadr',
                'description' => 'SES Kadr Viloyat',
                'type' => RoleTypeEnum::REGION
            ],
            [
                'name' => 'FVV kadr',
                'description' => 'MCHS  Kadr Respublika',
                'type' => RoleTypeEnum::REPUBLIC
            ],
            [
                'name' => 'FVB kadr',
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
        ];


        foreach ($meta as $role) {
            Role::query()->create([
                'name' => $role['name'],
                'description' => $role['description'],
                'type' => $role['type'],
            ]);
        }

        $role1 = Role::query()->find(1)->update([
            'children' => [2, 3, 4, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,26]
        ]);
        $role2 = Role::query()->find(2)->update([
            'children' => [3, 4, 11, 13, 15, 16, 17, 18, 19, 21, 23]
        ]);
        $role20 = Role::query()->find(20)->update([
            'children' => [21, 16]
        ]);
        $role21 = Role::query()->find(21)->update([
            'children' => [16]
        ]);
        $role22 = Role::query()->find(22)->update([
            'children' => [23 , 15]
        ]);
        $role23 = Role::query()->find(23)->update([
            'children' => [15]
        ]);
        $role24 = Role::query()->find(24)->update([
            'children' => [17]
        ]);
        $role25 = Role::query()->find(25)->update([
            'children' => [18]
        ]);
        $role26 = Role::query()->find(26)->update([
            'children' => [19]
        ]);
    }
}
