<?php

namespace Database\Seeders;

use App\Models\CheckListStatus;
use Illuminate\Database\Seeder;

class CheckListStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CheckListStatus::create([
            'name' => '1-tasdiqlash',
        ]);

        CheckListStatus::create([
            'name' => '2- tasdiqlash',
        ]);


        CheckListStatus::create([
            'name' => 'E\'tiroz bildirilgan',
        ]);

        CheckListStatus::create([
            'name' => 'Tasdiqlangan',
        ]);
    }
}
