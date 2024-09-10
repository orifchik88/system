<?php

namespace Database\Seeders;

use App\Models\Sphere;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SphereSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = getData(config('app.gasn.sphere'));

        foreach ($data['data']['data'] as $sphere) {
            Sphere::create([
                'id' => $sphere['id'],
                'name_uz' => $sphere['name_uz'],
                'name_ru' => $sphere['name_ru'],
            ]);
        }
    }
}
