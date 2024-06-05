<?php

namespace Database\Seeders;

use App\Models\ClientType;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
//            UserSeeder::class,
//            RolePermissionSeeder::class,
//                UserStatusSeeder::class
        ]);
//         UserType::factory(100)->create();
//        ClientType::factory(50)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
