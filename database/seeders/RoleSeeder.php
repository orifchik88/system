<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meta[] = [
            'Registrator' => 'Registrator',
            'Inspector' => 'Inspector',
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
            'Viloyat kuzatuvchi' => 'Viloyat kuzatuvchi',

        ];
    }
}
