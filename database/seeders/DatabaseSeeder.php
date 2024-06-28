<?php

namespace Database\Seeders;

use App\Models\ClientType;
use App\Models\ResponseSector;
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
            AppearanceTypeSeeder::class,
            FundingSourceSeeder::class,
            ObjectStatusSeeder::class,
            DifficultyCategorySeeder::class,
            ConstructionTypesSeeder::class,
            ObjectTypeSeeder::class,
            ObjectSectorSeeder::class,
            ResponseStatusSeeder::class,
            AdministrativeStatusSeeder::class,
            RolePermissionSeeder::class,
            UserStatusSeeder::class
        ]);

        UserType::factory(10)->create();
        ClientType::factory(10)->create();

    }
}
