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
        $users = [
            [
                'name' => 'Inspector',
                'login' => 'inspector',
                'phone' => '+998917894512',
                'pinfl' => '1234567894551',
                'password' => 'inspector',
                'user_status_id' => 6,
                'role' => 'inspector'
            ],
            [
                'name' => 'Super Admin',
                'login' => 'superadmin',
                'phone' => '+998337071727',
                'pinfl' => '1234567894551',
                'password' => 'admin',
                'user_status_id' => 6,
                'role' => 'admin'
            ],
            [
                'name' => 'Register',
                'login' => 'register',
                'phone' => '+998941234567',
                'pinfl' => '1234567894551',
                'password' => 'register',
                'user_status_id' => 6,
                'role' => 'register'
            ],

        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'login' => $userData['login'],
                'pinfl' => $userData['pinfl'],
                'password' => $userData['password'],
                'user_status_id' => $userData['user_status_id'],
            ]);
            $user->assignRole($userData['role']);
        }
    }
}
