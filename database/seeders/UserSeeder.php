<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resKadr = User::query()->create([
            'name' => 'Res Kadr',
            'login' => 'reskadr',
            'phone' => '+998917894512',
            'pinfl' => '31910932610085',
            'password' => 'reskadr',
            'user_status_id' => 1,
        ]);

        $resKadr->roles()->attach(1);

    }
}
