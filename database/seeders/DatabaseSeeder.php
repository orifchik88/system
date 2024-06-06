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
            RolePermissionSeeder::class,
            UserStatusSeeder::class
        ]);

//        UserType::factory(100)->create();
//        ClientType::factory(50)->create();

    }
}
