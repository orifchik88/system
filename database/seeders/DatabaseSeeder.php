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
            ActStatusSeeder::class,
            RegulationStatusSeeder::class,
            RegulationTypeSeeder::class,
            RegionSeeder::class,
            RolePermissionSeeder::class,
            UserStatusSeeder::class,
            UserSeeder::class,
            AppearanceTypeSeeder::class,
            FundingSourceSeeder::class,
            ObjectStatusSeeder::class,
            DifficultyCategorySeeder::class,
            ConstructionTypesSeeder::class,
            ObjectTypeSeeder::class,
            ObjectSectorSeeder::class,
            ResponseStatusSeeder::class,
            AdministrativeStatusSeeder::class,
            LevelSeeder::class,
            QuestionSeeder::class,
        ]);

        UserType::factory(10)->create();
        ClientType::factory(10)->create();
    }
}
