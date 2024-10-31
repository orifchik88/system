<?php

namespace App\Console\Commands;

use App\Enums\DifficultyCategoryEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\ObjectTypeEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Article;
use App\Models\ArticleUser;
use App\Models\Block;
use App\Models\District;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEmployee;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->migrateUsers();
    }

    private function migrateObjects()
    {
        $objects = DB::connection('third_pgsql')->table('objects')
            ->where('is_migrated', false)
            ->where('region_id', 'c053cdb4-94f6-450f-9da9-f0bf2c145587')
            ->limit(200)->get();

        $objectType = [
            '79f40f51-0368-4b6c-8326-f83d0453a848' => ObjectTypeEnum::LINEAR,
            '8517f8fd-1cd5-446c-8a7d-43939167e78c' => ObjectTypeEnum::BUILDING
        ];

        $objectStatus = [
            'e4bdf226-dae8-46aa-a152-38c4d19889f5' => ObjectStatusEnum::PROGRESS,
            '38a25938-d040-4ccf-9e64-23f483c53e3b' => ObjectStatusEnum::NEW,
            'b50a4eaa-9f68-40ae-83ab-e1971c0ea114' => ObjectStatusEnum::FROZEN,
            'd2fd8089-d7e3-43cc-afe8-9080bf9c0107' => ObjectStatusEnum::SUSPENDED,
            'be3623e7-78f5-48f6-8135-edf3731a838c' => ObjectStatusEnum::SUBMITTED
        ];

        $objectDifficulty = [
            '00e3b087-214f-4e11-8399-62dccc3e145b' => 'IV',
            'dfcc471f-2d3e-4258-93a9-b9893668e827' => 'I',
            'e8c76645-3e36-4e53-9867-e0b981f065e6' => 'II',
            '9b54fb13-a7d4-4b7c-8ca3-c876c2cedb1a' => 'III'
        ];

        foreach ($objects as $object) {
            $organization = DB::connection('second_pgsql')->table('organizations')
                ->where('id', $object->organization_id)
                ->first();
            $checkObject = Article::query()->where('old_id', $object->id)->first();
            if ($checkObject != null)
                continue;

            $region = Region::query()->where('old_id', $object->region_id)->first();
            $district = District::query()->where('old_id', $object->district_id)->first();

            $fundingSource = [
                'b971a882-963d-4a28-a9cd-4b8daf4e792a' => 1,
                '41cade7c-c473-4922-8952-52787ba56a25' => 3

            ];
            $users = DB::connection('third_pgsql')->table('participants_construction')
                ->where('status', true)
                ->where('object_id', $object->id)
                ->get();

            $article = new Article();
            $article->name = $object->name;
            $article->region_id = $region->id;
            $article->district_id = $district->id;
            $article->object_status_id = $objectStatus[$object->object_status_id];
            $article->object_type_id = $objectType[$object->object_type_id];
            $article->organization_name = $organization->name;
            $article->location_building = $object->location_building;
            $article->address = $object->address;
            $article->cadastral_number = $object->cadastral_number;
            $article->name_expertise = $object->name_expertise;
            $article->difficulty_category_id = DifficultyCategoryEnum::fromString($objectDifficulty[$object->difficulty_category_id]);
            $article->construction_cost = $object->construction_cost;
            $article->sphere_id = null;
            $article->program_id = null;
            $article->construction_works = $object->construction_works;
            $article->linear_type = null;
            $article->appearance_type_id = 1;
            $article->is_accepted = true;
            $article->organization_projects = $object->organization_projects;
            $article->specialists_certificates = $object->specialists_certificates;
            $article->contract_file = $object->contract_file;
            $article->confirming_laboratory = $object->confirming_laboratory;
            $article->file_energy_efficiency = $object->file_energy_efficiency;
            $article->legal_opf = $object->legal_opf;
            $article->lat = $object->lat;
            $article->long = $object->long;
            $article->dxa_response_id = null;
            $article->price_supervision_service = price_supervision($object->cost);
            $article->task_id = $object->task_id;
            $article->number_protocol = $object->number_protocol;
            $article->positive_opinion_number = $object->positive_opinion_number;
            $article->date_protocol = $object->date_protocol;
            $article->funding_source_id = $fundingSource[$object->funding_source_id];
            $article->paid = 0;
            $article->payment_deadline = Carbon::now();
            $article->deadline = $object->deadline;
            $article->gnk_id = $object->gnk_id;
            $article->reestr_number = (int)$object->reestr_number;
            $article->old_id = $object->id;
            $article->save();

            foreach ($users as $user) {
                $role = Role::query()->where('old_id', $user->role_id)->first();
                $userDb = User::query()->where('old_id', $user->user_id)->first();
                $userRole = new ArticleUser();
                $userRole->role_id = $role->id;
                $userRole->article_id = $article->id;
                $userRole->user_id = $userDb->id;

                $userRole->save();
            }

            $blockArr = json_decode($object->blocks, true);
            foreach ($blockArr as $item) {
                $block = DB::connection('third_pgsql')->table('blocks')
                    ->where('id', $item)
                    ->first();
                $blockModel = new Block();
                $blockModel->name = $block->name;
                $blockModel->block_number = $block->number;
                $blockModel->block_mode_id = null;
                $blockModel->block_type_id = null;
                $blockModel->article_id = $article->id;
                $blockModel->floor = null;
                $blockModel->construction_area = null;
                $blockModel->count_apartments = null;
                $blockModel->height = null;
                $blockModel->length = null;
                $blockModel->status = false;
                $blockModel->appearance_type = null;
                $blockModel->accepted = $block->is_send;
                $blockModel->dxa_response_id = null;
                $blockModel->created_at = $block->created_at;
                $blockModel->deleted_at = $block->deleted_at;
                $block->save();
            }

            DB::connection('third_pgsql')->table('objects')
                ->where('id', $object->id)
                ->update(
                    [
                        'is_migrated' => true
                    ]
                );

        }
    }

    private function migrateUsers()
    {
        $users = DB::connection('second_pgsql')->table('user')
            ->where('is_migrated', false)
            ->where('active', 1)
            ->get()
            ->random(100);

        $userStatuses = [
            '30165510-1d9d-4d6e-bcc5-6246af0cbc22' => UserStatusEnum::ACTIVE,
            'cb4ab3d0-0721-4d24-a459-12aefaebb12b' => UserStatusEnum::ON_HOLIDAY,
            'c67af18e-c131-4bfe-9aa5-1444b6003fd5' => UserStatusEnum::ON_HOLIDAY,
            '31356f88-8c96-4abb-9e68-9410d6fe4223' => UserStatusEnum::ON_HOLIDAY,
            '7418c1ac-6d28-44ab-8e30-7125125fda88' => UserStatusEnum::ON_HOLIDAY,
            'f386319c-7717-402e-9529-815c4cc95c8f' => UserStatusEnum::RELEASED
        ];

        $inspectorRoles = [
            '80b740c4-79ef-4c45-a76a-926f90fa3780' => UserRoleEnum::INSPECTOR,
            '2316d2ab-ae0b-497b-9175-642b414c1886' => UserRoleEnum::INSPECTOR,
            'db60cbca-8d2f-4911-9fdc-6fc30102c669' => UserRoleEnum::INSPECTOR
        ];

        foreach ($users as $userO) {
            $userStatus = isset($userStatuses[$userO->status_id]) ? $userStatuses[$userO->status_id] : UserStatusEnum::ACTIVE;
            $region = Region::query()->where('old_id', $userO->region_id)->first();
            $district = District::query()->where('old_id', $userO->district_id)->first();
            $role = Role::query()->where('old_id', $userO->role_id)->first();
            $checkUser = User::query()->where('old_id', $userO->id)->first();

            if ($checkUser != null) {
                DB::connection('second_pgsql')->table('user')
                    ->where('id', $userO->id)
                    ->update(
                        [
                            'is_migrated' => true
                        ]
                    );

                continue;
            }

            if ($role == null && !isset($inspectorRoles[$userO->role_id]))
                continue;

            $role_id = isset($inspectorRoles[$userO->role_id]) ? $inspectorRoles[$userO->role_id] : $role->getAttributes()['id'];

            $user = new User();
            $user->name = $userO->name;
            $user->phone = $userO->phone;
            $user->surname = $userO->surname;
            $user->middle_name = $userO->middle_name;
            $user->region_id = ($region != null) ? $region->id : null;
            $user->district_id = ($district != null) ? $district->id : null;
            $user->pinfl = ($userO->pinfl != null) ? $userO->pinfl : $userO->stir_org;
            $user->password = Hash::make($userO->phone);
            $user->active = 1;
            $user->login = $userO->login;
            $user->password = Hash::make($userO->phone);
            $user->user_status_id = $userStatus;
            $user->organization_name = $userO->organization_name;
            $user->identification_number = $userO->stir_org;
            $user->old_id = $userO->id;
            $user->save();


            UserRole::query()->create([
                'user_id' => $user->id,
                'role_id' => $role_id
            ]);

            DB::connection('second_pgsql')->table('user')
                ->where('id', $userO->id)
                ->update(
                    [
                        'is_migrated' => true
                    ]
                );
        }
    }
}
