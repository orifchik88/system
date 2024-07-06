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
                'name' => 'Super Admin',
                'login' => 'superadmin',
                'pinfl' => '1234567894551',
                'password' => 'admin',
                'user_status_id' => 6,
                'role' => 'admin'
            ],
            [
                'name' => 'Register',
                'login' => 'register',
                'pinfl' => '1234567894551',
                'password' => 'register',
                'user_status_id' => 6,
                'role' => 'register'
            ],
            [
                'name' => 'Inspector',
                'login' => 'inspector',
                'pinfl' => '1234567894551',
                'password' => 'inspector',
                'user_status_id' => 6,
                'role' => 'inspector'
            ]
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
