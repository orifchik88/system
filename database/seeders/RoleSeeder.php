<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meta = [
            'Respublika Kadr' => 'Respublika Kadr',
            'Viloyat Kadr' => 'Viloyat Kadr',
            'Inspector' => 'Inspector',
            'Registrator' => 'Registrator',
            'Quruvchi' => 'Ichki nazorat',
            'Buyurtmachi' => 'Texnik nazorat',
            'Operator' => 'Operator',
            'Boshliq' => 'Boshliq',
            'Viloyat kuzatuvchi' => 'Viloyat kuzatuvchi',
            'Respublika kuzatuvchi' => 'Respublika kuzatuvchi',
            'O\'t o\'chirish' => 'O\'t o\'chirish',
            'SES' => 'SES',
            'Nogironlar jamiyati' => 'Nogironlar jamiyati',
            'Nogironlar assotsatsiyasi' => 'Nogironlar assotsatsiyasi',
            'Uy-joy inspeksiyasi' => 'Uy-joy inspeksiyasi',
            'SES Respublika Kadr' => 'SES Respublika Kadr',
            'SES Viloyat Kadr' => 'SES Viloyat Kadr',
            'MCHS Respublika Kadr' => 'MCHS Respublika Kadr',
            'MCHS Viloyat Kadr' => 'MCHS Viloyat Kadr',
            'Respublika Nogironlar Jamiyati Kadr' => 'Respublika Nogironlar Jamiyati Kadr',
            'Respublika Nogironlar Assotsatsiya Kadr' => 'Respublika Nogironlar Assotsatsiya Kadr',
            'Respublika Uy-Joy Inspeksiya Kadr' => 'Respublika Uy-Joy Inspeksiya Kadr'
        ];

        foreach ($meta as $key => $role) {
            Role::query()->create([
                'name' => $key,
                'description' => $role
            ]);
        }
    }
}
