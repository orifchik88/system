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
           'name' => 'Tasdiqlangan',
        ]);

        CheckListStatus::create([
            'name' => 'Qisman tasdiqlangan',
        ]);

        CheckListStatus::create([
            'name' => 'To\'ldirilmagan',
        ]);


        CheckListStatus::create([
            'name' => 'E\'tiroz bildirilgan',
        ]);

        CheckListStatus::create([
            'name' => 'To\'ldirilgan',
        ]);
    }
}
