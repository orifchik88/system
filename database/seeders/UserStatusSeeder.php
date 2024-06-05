<?php

namespace Database\Seeders;

use App\Models\UserStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meta = [
           "Qo'shimcha ta'tili",
            "Kasalllik ta'tili",
            "Sayohat ta'tili",
            "O'qish ta'tili",
            "Sababsiz",
            "Faol",
            "Nofaol",
            "Bo'shatilgan",
        ];

        foreach ($meta as $value) {
            UserStatus::create(['status' => $value]);
        }
    }
}
