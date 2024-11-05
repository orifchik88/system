<?php

namespace App\Console\Commands;

use App\Enums\DifficultyCategoryEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\ObjectTypeEnum;
use App\Enums\RegulationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\ActStatus;
use App\Models\ActViolation;
use App\Models\Article;
use App\Models\ArticleUser;
use App\Models\Block;
use App\Models\District;
use App\Models\Region;
use App\Models\Regulation;
use App\Models\RegulationUser;
use App\Models\RegulationViolation;
use App\Models\Response;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEmployee;
use App\Models\UserRole;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migration {--type=1}';

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
        switch ($this->option('type')) {
            case 1:
                $this->migrateUsers();
                break;
            case 2:
                $this->migrateObjects();
                break;
            case 3:
                $this->migrateRegulations();
                break;
            case 4:
                $this->migrateUsers();
                $this->migrateObjects();
                $this->migrateRegulations();
                break;
            default:
                echo 'Fuck you!';
                break;
        }
    }

    private function migrateRegulations()
    {
        $objects = Article::query()
            ->with('users')
            ->where('is_regulation_get', false)
            ->whereNotNull('old_id')
            ->limit(20)
            //->whereIn('old_id', ['55934137-2947-42de-ab4f-401d6a4ead46','670aaba7-af23-42f8-aa2a-36044e829d65'])
            ->get();

        $regulationStatuses = [
            0 => RegulationStatusEnum::PROVIDE_REMEDY,
            1 => RegulationStatusEnum::PROVIDE_REMEDY,
            2 => RegulationStatusEnum::CONFIRM_REMEDY,
            3 => RegulationStatusEnum::ATTACH_DEED,
            4 => RegulationStatusEnum::CONFIRM_DEED,
            5 => RegulationStatusEnum::CONFIRM_DEED_CMR,
            6 => RegulationStatusEnum::CONFIRM_DEED_CMR,
            7 => RegulationStatusEnum::ELIMINATED,
            8 => RegulationStatusEnum::IN_LAWYER
        ];

        $actStatuses = [
            '78f19080-d9b6-4e8d-887f-03055130e213' => 1,
            '2ed33411-2930-413d-8833-3af9d0fc5de4' => 2,
            'e6c61e8f-daea-4716-81d0-4be120264e15' => 3,
            '36c43564-1861-48f9-bdfe-9158289ed94c' => 4,
            'b4e1ed17-239a-4422-b671-14551d4aca39' => 5,
            '26c0338a-b18b-4ae4-9eb2-4a9ace9a65a1' => 6,
            '6b483bf3-100c-4086-b421-1e329a00c0b6' => 10,
            '90966d39-154a-4bec-9903-506f97eb6156' => 11,
            'd042d139-f8a3-4f3d-ada2-f5d179a811b2' => 12,
            '97f99deb-0807-48d3-9eea-b2cfda114137' => 8,
        ];

        $actViolationTypes = [
            '41a6378c-b3f1-453b-bb4f-9538c2309348' => 1,
            '53485e56-899c-4615-bc6e-4ec76a9bfaca' => 2,
            'b8f8f00d-4c3d-48d6-800c-9fffd2394777' => 3,
        ];

        $inspectorRoles = [
            '80b740c4-79ef-4c45-a76a-926f90fa3780' => UserRoleEnum::INSPECTOR,
            '2316d2ab-ae0b-497b-9175-642b414c1886' => UserRoleEnum::INSPECTOR,
            'db60cbca-8d2f-4911-9fdc-6fc30102c669' => UserRoleEnum::INSPECTOR
        ];

        foreach ($objects as $object) {
            $oldObject = DB::connection('third_pgsql')->table('objects')
                ->where('id', $object->old_id)
                ->first();
            $regulations = DB::connection('third_pgsql')->table('regulations')
                ->where('object_id', $oldObject->id)
                ->get();

            if (!$oldObject)
                continue;

            foreach ($regulations as $regulation) {
                $role = Role::query()->where('old_id', $regulation->created_by_role_id)->first();
                $user = User::query()->where('old_id', $regulation->created_by)->first();
                $violations = DB::connection('third_pgsql')->table('violations')
                    ->where('regulation_id', $regulation->id)
                    ->get();

                $regulationStatus = $regulationStatuses[$regulation->phase];
                if ($regulation->is_administrative && in_array($regulation->phase, [1, 2, 3, 4, 8]))
                    $regulationStatus = RegulationStatusEnum::IN_LAWYER;

                if ($regulation->is_administrative && $regulation->phase == 7)
                    $regulationStatus = RegulationStatusEnum::LATE_EXECUTION;
                $toUserRoleId = [
                    3 => UserRoleEnum::ICHKI,
                    2 => UserRoleEnum::MUALLIF,
                    1 => UserRoleEnum::TEXNIK
                ];
                $toUserRole = explode('/', $regulation->regulation_number)[1];

                if(User::query()->where('old_id', $regulation->user_id)->first() == null)
                    continue;

                $newRegulation = Regulation::create([
                    'object_id' => $object->id,
                    'deadline' => $regulation->deadline,
                    'checklist_id' => null,
                    'question_id' => null,
                    'regulation_status_id' => $regulationStatus,
                    'regulation_number' => $regulation->regulation_number,
                    'act_status_id' => ($regulation->act_status_id != null) ? $actStatuses[$regulation->act_status_id] : null,
                    'regulation_type_id' => 1,
                    'created_by_role_id' => (isset($inspectorRoles[$regulation->created_by_role_id])) ? $inspectorRoles[$regulation->created_by_role_id] : $role->id,
                    'created_by_user_id' => $user->id,
                    'user_id' => User::query()->where('old_id', $regulation->user_id)->first()->id,
                    'monitoring_id' => null,
                    'deadline_asked' => $regulation->deadline_asked,
                    'created_at' => $regulation->created_at,
                    'role_id' => $toUserRoleId[$toUserRole],
                ]);
                RegulationUser::create([
                    'regulation_id' => $newRegulation->id,
                    'from_user_id' => $newRegulation->created_by_user_id,
                    'from_role_id' => $newRegulation->created_by_role_id,
                    'to_user_id' => $newRegulation->user_id,
                    'to_role_id' => $newRegulation->role_id,
                ]);

                foreach ($violations as $violation) {
                    $actViolations = DB::connection('third_pgsql')->table('violation_act')
                        ->where('violation_id', $violation->id)
                        ->get();

                    $newViolation = Violation::create([
                        'question_id' => null,
                        'title' => $violation->title,
                        'description' => $violation->description,
                        'bases_id' => null,
                        'checklist_id' => null,
                        'created_at' => $violation->created_at,
                    ]);
                    RegulationViolation::create([
                        'regulation_id' => $newRegulation->id,
                        'violation_id' => $newViolation->id
                    ]);

                    foreach ($actViolations as $actViolation) {
                        $actUser = User::query()->where('old_id', $actViolation->user_id)->first();
                        if($actUser == null)
                            continue;

                        $articleUserRole = ArticleUser::query()->where('article_id', $object->id)->where('user_id', $actUser->id)->first();
                        if($articleUserRole == null)
                            continue;

                        $actViolationStatus = ActViolation::PROGRESS;
                        if(in_array($newRegulation->act_status_id, [2, 5, 8, 11, 13]))
                            $actViolationStatus = ActViolation::ACCEPTED;
                        if(in_array($newRegulation->act_status_id, [3, 6, 9, 12]))
                            $actViolationStatus = ActViolation::REJECTED;

                        ActViolation::create([
                            'regulation_id' => $newRegulation->id,
                            'regulation_violation_id' => $newViolation->id,
                            'user_id' => $actUser->id,
                            'act_status_id' => $newRegulation->act_status_id,
                            'comment' => $actViolation->comment,
                            'role_id' => $articleUserRole->role_id,
                            'created_at' => $actViolation->created_at,
                            'act_violation_type_id' => $actViolationTypes[$actViolation->type_id],
                            'status' => $actViolationStatus,
                        ]);
                    }

                    DB::connection('third_pgsql')->table('violations')
                        ->where('id', $violation->id)
                        ->update(
                            [
                                'is_migrated' => true
                            ]
                        );
                }

                DB::connection('third_pgsql')->table('regulations')
                    ->where('id', $regulation->id)
                    ->update(
                        [
                            'is_migrated' => true
                        ]
                    );
            }

            $object->update([
                'is_regulation_get' => true
            ]);
        }
    }

    private function migrateObjects()
    {
        $objects = DB::connection('third_pgsql')->table('objects')
            ->where('is_migrated', false)
            ->where('region_id', 'c053cdb4-94f6-450f-9da9-f0bf2c145587')
            ->limit(20)
            ->get();

        $objectType = [
            '79f40f51-0368-4b6c-8326-f83d0453a848' => ObjectTypeEnum::LINEAR,
            '8517f8fd-1cd5-446c-8a7d-43939167e78c' => ObjectTypeEnum::BUILDING
        ];

        $objectStatus = [
            'e4bdf226-dae8-46aa-a152-38c4d19889f5' => ObjectStatusEnum::PROGRESS,
            '38a25938-d040-4ccf-9e64-23f483c53e3b' => ObjectStatusEnum::PROGRESS,
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

        $orgRoles = [
            'e7777bfa-7416-44e8-b609-99136ec5d3b0' => UserRoleEnum::LOYIHA,
            '6126cbeb-b0b8-4059-9758-14ff3c35473f' => UserRoleEnum::BUYURTMACHI,
            'b5392622-4180-4c89-9b4e-2976f05b9150' => UserRoleEnum::QURILISH
        ];

        $inspectorRoles = [
            '80b740c4-79ef-4c45-a76a-926f90fa3780' => UserRoleEnum::INSPECTOR,
            '2316d2ab-ae0b-497b-9175-642b414c1886' => UserRoleEnum::INSPECTOR,
            'db60cbca-8d2f-4911-9fdc-6fc30102c669' => UserRoleEnum::INSPECTOR
        ];

        foreach ($objects as $object) {
            $customer = DB::connection('third_pgsql')->table('customers')
                ->where('id', $object->customer_id)
                ->first();
            $construction_type = DB::connection('third_pgsql')->table('construction_types')
                ->where('id', $object->construction_type_id)
                ->first();
            $checkObject = Article::query()->where('old_id', $object->id)->first();

            if ($checkObject != null) {
                DB::connection('third_pgsql')->table('objects')
                    ->where('id', $object->id)
                    ->update(
                        [
                            'is_migrated' => true
                        ]
                    );
                continue;
            }

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

            $canContinue = true;
            foreach ($users as $user) {
                $userDb = User::query()->where('old_id', $user->user_id)->first();
                $userCcnis = DB::connection('second_pgsql')->table('user')
                    ->where('id', $user->id)
                    ->first();

                if ($userDb == null && $userCcnis != null)
                    $canContinue = false;
            }

            if (!$canContinue)
                continue;

            $article = new Article();
            $article->name = $object->name;
            $article->region_id = $region->id;
            $article->district_id = $district->id;
            $article->object_status_id = $objectStatus[$object->object_status_id];
            $article->object_type_id = ($object->object_type_id == null) ? null : $objectType[$object->object_type_id];
            $article->organization_name = ($customer != null) ? $customer->full_name : null;
            $article->location_building = $object->location_building;
            $article->address = $object->address;
            $article->cadastral_number = $object->cadastral_number;
            $article->name_expertise = $object->name_expertise;
            $article->difficulty_category_id = DifficultyCategoryEnum::fromString($objectDifficulty[$object->difficulty_category_id]);
            $article->construction_cost = $object->construction_cost;
            $article->sphere_id = null;
            $article->program_id = null;
            $article->construction_works = ($construction_type != null) ? $construction_type->type : null;
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
            $article->price_supervision_service = price_supervision($object->construction_cost);
            $article->task_id = $object->task_id;
            $article->number_protocol = $object->number_protocol;
            $article->positive_opinion_number = $object->positive_opinion_number;
            $article->date_protocol = $object->date_protocol;
            $article->funding_source_id = ($object->funding_source_id != null) ? $fundingSource[$object->funding_source_id] : null;
            $article->paid = 0;
            $article->payment_deadline = Carbon::now();
            $article->deadline = ($objectStatus[$object->object_status_id] == ObjectStatusEnum::SUBMITTED) ? null : Carbon::parse($object->deadline)->format('Y-m-d');
            $article->gnk_id = $object->gnk_id;
            $article->reestr_number = (int)$object->reestr_number;
            $article->old_id = $object->id;
            $article->save();

            Response::query()->updateOrCreate(['task_id' => $object->task_id], [
                'module' => ($object->object_type_id == null) ? null : (($objectType[$object->object_type_id] == ObjectTypeEnum::BUILDING) ? 1 : 3),
                'api' => 'my_gov_uz',
                'status' => 0
            ]);

            foreach ($users as $user) {
                $userCcnis = DB::connection('second_pgsql')->table('user')
                    ->where('id', $user->id)
                    ->first();
                if ($userCcnis == null)
                    continue;

                $role = Role::query()->where('old_id', $user->role_id)->first();
                $userDb = User::query()->where('old_id', $user->user_id)->first();
                $userRole = new ArticleUser();
                $userRole->role_id = (isset($inspectorRoles[$user->role_id])) ? $inspectorRoles[$user->role_id] : $role->id;
                $userRole->article_id = $article->id;
                $userRole->user_id = $userDb->id;

                $userRole->save();
                if (in_array($user->role_id, ['b5392622-4180-4c89-9b4e-2976f05b9150', '6126cbeb-b0b8-4059-9758-14ff3c35473f', 'e7777bfa-7416-44e8-b609-99136ec5d3b0'])) {
                    $organization = DB::connection('second_pgsql')->table('organizations')
                        ->where('id', $user->organization_id)
                        ->first();
                    if ($organization == null)
                        continue;
                    $checkUser = User::where('pinfl', $organization->stir)->first();
                    if ($checkUser) {
                        $userRole = new ArticleUser();
                        $userRole->role_id = $orgRoles[$user->role_id];
                        $userRole->article_id = $article->id;
                        $userRole->user_id = $checkUser->id;

                        $userRole->save();

                    } else {
                        $insertUser = User::query()->create([
                            'name' => null,
                            'surname' => null,
                            'middle_name' => null,
                            'phone' => null,
                            'active' => 1,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'login' => $organization->stir,
                            'organization_name' => $organization->name,
                            'password' => bcrypt($organization->stir),
                            'user_status_id' => UserStatusEnum::ACTIVE,
                            'pinfl' => $organization->stir,
                            'identification_number' => $organization->stir,
                        ]);

                        UserRole::query()->create([
                            'user_id' => $insertUser->id,
                            'role_id' => $orgRoles[$user->role_id],
                        ]);

                        $userRole = new ArticleUser();
                        $userRole->role_id = $orgRoles[$user->role_id];
                        $userRole->article_id = $article->id;
                        $userRole->user_id = $insertUser->id;

                        $userRole->save();
                    }

                }

            }

            $blockArr = json_decode($object->blocks, true);
            if (is_array($blockArr)) {
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
                    $blockModel->save();
                }
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
            ->random(200);

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
