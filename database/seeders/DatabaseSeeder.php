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
            NormativeDocumentSeeder::class,
            BasisSeeder::class,
            TopicSeeder::class,
            ObjectTypeSeeder::class,
            CheckListStatusSeeder::class,
            WorkTypeSeeder::class,
            RoleSeeder::class,
            QuestionSeeder::class,
            LevelStatusSeeder::class,
            LawyerStatusSeeder::class,
            RegionSeeder::class,
            ResponseStatusSeeder::class,
            AdministrativeStatusSeeder::class,
            BlockModeSeeder::class,
            BlockTypeSeeder::class,
            ActViolationTypeSeeder::class,
            ActStatusSeeder::class,
            RegulationStatusSeeder::class,
            RegulationTypeSeeder::class,
            UserStatusSeeder::class,
            UserSeeder::class,
            AppearanceTypeSeeder::class,
            FundingSourceSeeder::class,
            SphereSeeder::class,
            ProgramSeeder::class,
            RekvizitSeeder::class,
            ObjectStatusSeeder::class,
            DifficultyCategorySeeder::class,
            ConstructionTypesSeeder::class,
            ObjectSectorSeeder::class,
        ]);
    }
}
