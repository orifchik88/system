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
        User::create([
            'name' => 'Shahzod',
            'email' => 'developer@mail.com',
            'password' => bcrypt('password')
        ]);
    }
}
