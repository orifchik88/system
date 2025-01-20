<?php

namespace App\Console\Commands;

use App\Enums\DifficultyCategoryEnum;
use App\Enums\LawyerStatusEnum;
use App\Enums\LogType;
use App\Enums\ObjectStatusEnum;
use App\Enums\ObjectTypeEnum;
use App\Enums\RegulationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Exceptions\NotFoundException;
use App\Helpers\ClaimStatuses;
use App\Http\Requests\PinflRequest;
use App\Models\ActStatus;
use App\Models\ActViolation;
use App\Models\Article;
use App\Models\ArticleUser;
use App\Models\Block;
use App\Models\CheckListAnswer;
use App\Models\Claim;
use App\Models\ClaimMonitoring;
use App\Models\ClaimOrganizationReview;
use App\Models\District;
use App\Models\DxaResponse;
use App\Models\Monitoring;
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
use App\Services\ArticleService;
use App\Services\ClaimService;
use App\Services\HistoryService;
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
    protected $signature = 'app:migration {--type=1} {--region=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private ClaimService $claimService;
    private ArticleService $articleService;

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
                $this->migrateObjects($this->option('region'));
                break;
            case 3:
                $this->migrateRegulations();
                break;
            case 4:
                $this->migrateLatLong();
                break;
            case 5:
                $this->migrateMonitoring();
                break;
            case 6:
                $this->syncObjects();
                break;
            case 7:
                $this->manualMigrateRegulations('0124-0166733/1');
                break;
            case 8:
                $this->deletePhaseRegulations();
                break;
            case 9:
                $this->migrateObjectUsers(2686);
                break;
            case 10:
                $this->migrateBlocks(1691);
                break;
            case 11:
                $this->migrateClaims();
                break;
            case 12:
                $this->dxaResponseMygov(165929773);
                break;
            case 13:
                $this->checkRegulations();
                break;
            case 14:
                $this->migrateActViolations();
                break;
            case 15:
                $this->syncLawyerStatus();
                break;
            case 16:
                $this->migrateNewRegulations();
                break;
            case 17:
                $this->migratePayments();
                break;
            default:
                echo 'Fuck you!';
                break;
        }
    }

    public function __construct(
        ClaimService   $claimService,
        ArticleService $articleService
    )
    {
        parent::__construct();
        $this->claimService = $claimService;
        $this->articleService = $articleService;
    }

    private function migratePayments()
    {
        $objects = DB::connection('third_pgsql')->table('objects')
            ->where('paid', '>', 0)
            ->where('is_payment_sync', false)
            ->limit(100)
            ->get();

        foreach ($objects as $object) {
            $article = Article::query()->where('old_id', $object->id)->first();

            if (!$article)
                continue;

            $paid = $article->paymentLogs()
                ->get()
                ->sum(function ($log) {
                    return $log->content->additionalInfo->amount ?? 0;
                });

            $cost = (float)$article->price_supervision_service - ($object->paid + $paid);

            $meta = ['amount' => $object->paid, 'cost' => $cost];

            (new HistoryService('article_payment_logs'))->createHistory(
                guId: $article->id,
                status: $article->object_status_id->value,
                type: LogType::TASK_HISTORY,
                date: null,
                comment: "",
                additionalInfo: $meta
            );

            DB::connection('third_pgsql')->table('objects')
                ->where('id', $object->id)
                ->update(
                    [
                        'is_payment_sync' => true
                    ]
                );
        }
    }

    private function syncLawyerStatus()
    {
        $regulations = DB::connection('third_pgsql')->table('regulations')
            ->where('is_administration_closed', true)
            ->get();

        foreach ($regulations as $regulation) {
            $regModel = Regulation::query()->where('regulation_number', $regulation->regulation_number)->first();
            if (!$regModel)
                continue;

            $regModel->update(
                [
                    'lawyer_status_id' => LawyerStatusEnum::ADMINISTRATIVE
                ]
            );
        }
    }

    private function migrateActViolations()
    {
        $regulations = Regulation::query()
            ->whereRaw("TRIM(regulation_number) ~ '^[0-9]+(-[0-9]+)?/[0-9]+$'")
            ->whereNotIn('regulation_status_id', [RegulationStatusEnum::ELIMINATED, RegulationStatusEnum::LATE_EXECUTION])
            ->where('is_act_sync', false)
            ->limit(100)
            ->get();

        foreach ($regulations as $regulation) {
            $oldRegulation = DB::connection('third_pgsql')->table('regulations')
                ->where('regulation_number', $regulation->regulation_number)
                ->first();
            $violations = DB::connection('third_pgsql')->table('violations')
                ->where('regulation_id', $oldRegulation->id)
                ->where('is_migrated', true)
                ->get();

            $actViolationTypes = [
                '41a6378c-b3f1-453b-bb4f-9538c2309348' => 1,
                '53485e56-899c-4615-bc6e-4ec76a9bfaca' => 2,
                'b8f8f00d-4c3d-48d6-800c-9fffd2394777' => 3,
            ];

            $regulationViolationIds = RegulationViolation::query()->where('regulation_id', $regulation->id)->pluck('violation_id')->toArray();

            foreach ($violations as $violation) {
                $violationModel = Violation::query()
                    ->where('title', $violation->title)
                    ->where('description', $violation->description)
                    ->whereIn('id', $regulationViolationIds)
                    ->first();

                if (!$violationModel)
                    continue;

                $actViolations = DB::connection('third_pgsql')->table('violation_act')
                    ->where('violation_id', $violation->id)
                    ->get();

                foreach ($actViolations as $actViolation) {
                    $exists = ActViolation::query()
                        ->where('comment', $actViolation->comment)
                        ->where('regulation_id', $regulation->id)
                        ->where('act_violation_type_id', $actViolationTypes[$actViolation->type_id])
                        ->first();

                    if ($exists)
                        continue;

                    $actUser = User::query()->where('old_id', $actViolation->user_id)->first();
                    if ($actUser == null)
                        continue;

                    $articleUserRole = ArticleUser::query()->where('article_id', $regulation->object_id)->where('user_id', $actUser->id)->first();
                    if ($articleUserRole == null)
                        continue;

                    $actViolationStatus = ActViolation::PROGRESS;
                    if (in_array($regulation->act_status_id, [2, 5, 8, 11, 13]))
                        $actViolationStatus = ActViolation::ACCEPTED;
                    if (in_array($regulation->act_status_id, [3, 6, 9, 12]))
                        $actViolationStatus = ActViolation::REJECTED;

                    ActViolation::create([
                        'regulation_id' => $regulation->id,
                        'regulation_violation_id' => $violationModel->id,
                        'user_id' => $actUser->id,
                        'act_status_id' => $regulation->act_status_id,
                        'comment' => $actViolation->comment,
                        'role_id' => $articleUserRole->role_id,
                        'created_at' => $actViolation->created_at,
                        'act_violation_type_id' => $actViolationTypes[$actViolation->type_id],
                        'status' => $actViolationStatus,
                    ]);
                }
            }

            $regulation->update(
                [
                    'is_act_sync' => true
                ]
            );
        }
    }

    public function checkRegulations()
    {
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

        $regulations = Regulation::query()
            ->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER)
            ->where('is_sync', false)
            ->limit(500)
            ->get();

        foreach ($regulations as $regulation) {
            $oldRegulation = DB::connection('third_pgsql')->table('regulations')
                ->where('regulation_number', $regulation->regulation_number)
                ->first();

            if (!$oldRegulation) {
                $regulation->update(['is_sync' => true]);
                continue;
            }

            if ($oldRegulation->phase == null || $oldRegulation->phase == '0')
                continue;

            $regulationStatus = $regulationStatuses[$oldRegulation->phase];
            if ($oldRegulation->is_administrative && in_array($oldRegulation->phase, [1, 2, 3, 4, 8]))
                $regulationStatus = RegulationStatusEnum::IN_LAWYER;

            if ($oldRegulation->is_administrative && $oldRegulation->phase == 7)
                $regulationStatus = RegulationStatusEnum::LATE_EXECUTION;

            $regulation->update(['regulation_status_id' => $regulationStatus]);

            if ($oldRegulation->deadline >= Carbon::parse($regulation->deadline)->format('Y-m-d'))
                $regulation->update(['deadline' => $oldRegulation->deadline]);

            $regulation->update(['is_sync' => true]);
        }
    }

    /**
     * @throws NotFoundException
     */
    public function dxaResponseMygov(int $task_id)
    {
        $response = DxaResponse::query()->where('task_id', $task_id)->first();

        if ($response)
            $this->articleService->acceptResponse($response);

        echo 'Ok';
    }

    public function migrateClaims()
    {
        $responses = Response::query()->where('module', 505)->where('status', 0)->limit(20)->get();

        foreach ($responses as $response) {
            DB::beginTransaction();

            try {
                $taskFormGov = $this->claimService->getClaimFromApi($response->task_id);

                if (!$taskFormGov) {
                    $response->update(['status' => 2]);

                    DB::commit();
                    continue;
                }

                $claimModel = Claim::query()->with('monitoring')->where('guid', $response->task_id)->first();

                if ($claimModel->monitoring != null) {
                    $this->claimService->updateResponseStatus(
                        guId: $response->task_id,
                        status: ClaimStatuses::RESPONSE_WATCHED
                    );

                    DB::commit();
                    continue;
                }

                $oldClaim = DB::connection('third_pgsql')->table('claims')
                    ->where('mygov_id', $response->task_id)
                    ->first();

                if (!$oldClaim)
                    continue;

                $orgsActs = [
                    'ses_conclusion_act' => 16,
                    'mchs_conclusion_act' => 15,
                    'nogiron_conclusion_act' => 17,
                    'kvartira_conclusion_act' => 19
                ];

                $orgsStatus = [
                    'ses_conclusion_act' => 'ses_match',
                    'mchs_conclusion_act' => 'mchs_match',
                    'nogiron_conclusion_act' => 'nogiron_match',
                    'kvartira_conclusion_act' => 'kvartira_match'
                ];

                $article = Article::query()->where('old_id', $oldClaim->object_id)->first();

                $status = $claimModel->status;

                if (!in_array($status, [ClaimStatuses::TASK_STATUS_CONFIRMED, ClaimStatuses::TASK_STATUS_REJECTED, ClaimStatuses::TASK_STATUS_ORGANIZATION_REJECTED])) {
                    if ($oldClaim->status == 'organization')
                        $status = ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION;
                    if ($oldClaim->status == 'inspector')
                        $status = ClaimStatuses::TASK_STATUS_INSPECTOR;
                    if ($oldClaim->status == 'connecting')
                        $status = ClaimStatuses::TASK_STATUS_ATTACH_OBJECT;
                    if ($oldClaim->status == 'checked')
                        $status = ClaimStatuses::TASK_STATUS_OPERATOR;
                    if ($oldClaim->status == 'inspection')
                        $status = ClaimStatuses::TASK_STATUS_DIRECTOR;
                    if ($oldClaim->status == 'inspection')
                        $status = ClaimStatuses::TASK_STATUS_DIRECTOR;
                }

                if ($status != ClaimStatuses::TASK_STATUS_ANOTHER) {
                    $blocksJson = json_decode($oldClaim->blocks, true);
                    $blocksArr = [];
                    foreach ($blocksJson as $item) {
                        $oldBlock = DB::connection('third_pgsql')->table('blocks')
                            ->where('id', $item)
                            ->first();
                        $blockModel = Block::query()
                            ->where('name', $oldBlock->name)
                            ->where('block_number', $oldBlock->number)
                            ->where('article_id', $article->id)
                            ->first();

                        if ($blockModel != null && $status == ClaimStatuses::TASK_STATUS_CONFIRMED)
                            $blockModel->update(['accepted' => true]);

                        if ($blockModel)
                            $blocksArr[] = $blockModel->id;
                    }

                    $organizationArray = [];
                    $orgJson = json_decode($oldClaim->object_info, true);
                    $oldNogironAssosatsiya = DB::connection('third_pgsql')->table('organization_reviews_blocks')
                        ->where('claim_id', $oldClaim->id)
                        ->where('organization_name', "Nogironlar assotsiatsiyasi")
                        ->whereNull('deleted_at')
                        ->first();

                    if (isset($orgJson['ses_conclusion_act']) && strlen($orgJson['ses_conclusion_act'] > 0))
                        $organizationArray[] = $orgsActs['ses_conclusion_act'];
                    if (isset($orgJson['mchs_conclusion_act']) && strlen($orgJson['mchs_conclusion_act'] > 0))
                        $organizationArray[] = $orgsActs['mchs_conclusion_act'];
                    if (isset($orgJson['nogiron_conclusion_act']) && strlen($orgJson['nogiron_conclusion_act'] > 0))
                        $organizationArray[] = $orgsActs['nogiron_conclusion_act'];
                    if (isset($orgJson['kvartira_conclusion_act']) && strlen($orgJson['kvartira_conclusion_act'] > 0))
                        $organizationArray[] = $orgsActs['kvartira_conclusion_act'];
                    if ($oldNogironAssosatsiya != null)
                        $organizationArray[] = 18;

                    $buildings = [];
                    if (in_array($status, [ClaimStatuses::TASK_STATUS_OPERATOR, ClaimStatuses::TASK_STATUS_DIRECTOR, ClaimStatuses::TASK_STATUS_CONFIRMED])) {
                        if ($oldClaim->html_table != null) {
                            $html = $oldClaim->html_table;

                            preg_match_all('/<tbody>(.*?)<\/tbody>/s', $html, $tbodyMatches);
                            $tbodyContent = $tbodyMatches[1][0] ?? '';

                            preg_match_all('/<tr>(.*?)<\/tr>/s', $tbodyContent, $rowMatches);

                            $data = [];
                            foreach ($rowMatches[1] as $row) {
                                preg_match_all('/<td.*?>(.*?)<\/td>/s', $row, $cellMatches);
                                $rowData = array_map('strip_tags', $cellMatches[1]); // HTML teglarini olib tashlash
                                $data[] = $rowData;
                            }

                            if (count($data) > 0) {
                                foreach ($data as $datum) {
                                    $building = [
                                        'name' => $datum[0],
                                        'cadaster' => $datum[1],
                                        'building_number' => $datum[2],
                                        'total_area' => $datum[3],
                                        'total_use_area' => $datum[4],
                                        'living_area' => $datum[5],
                                        'area' => $datum[6],
                                    ];

                                    $buildings[] = $building;
                                }
                            }
                        }

                        if ($oldClaim->inspector_comment != null)
                            (new HistoryService('claim_histories'))->createHistory(
                                guId: $response->task_id,
                                status: ClaimStatuses::TASK_STATUS_OPERATOR,
                                type: LogType::TASK_HISTORY,
                                date: null,
                                comment: $oldClaim->inspector_comment
                            );
                    }

                    $monitoring = ClaimMonitoring::query()->create(
                        [
                            'blocks' => json_encode($blocksArr),
                            'organizations' => json_encode($organizationArray),
                            'claim_id' => $claimModel->id,
                            'inspector_answer' => ($status == ClaimStatuses::TASK_STATUS_OPERATOR || $status == ClaimStatuses::TASK_STATUS_DIRECTOR) ? 20 : 0,
                            'operator_answer' => count($buildings) > 0 ? base64_encode(gzcompress(json_encode($buildings), 9)) : null,
                            'object_id' => $article->id
                        ]
                    );

                    foreach ($organizationArray as $item) {
                        if ($item == 18)
                            continue;

                        $orgNameTag = explode('_', array_search($item, $orgsActs));
                        $requestData = [
                            $orgNameTag[0] . "_match" => $orgJson[$orgNameTag[0] . "_match"],
                            $orgNameTag[0] . "_territory" => $orgJson[$orgNameTag[0] . "_territory"],
                            $orgNameTag[0] . "_name" => $orgJson[$orgNameTag[0] . "_name"],
                            $orgNameTag[0] . "_conclusion_act" => str_replace('<br/>', '', $orgJson[$orgNameTag[0] . "_conclusion_act"]),
                            $orgNameTag[0] . "_datetime" => \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s')
                        ];

                        ClaimOrganizationReview::query()->create(
                            [
                                'claim_id' => $claimModel->id,
                                'monitoring_id' => $monitoring->id,
                                'organization_id' => $item,
                                'answered_at' => Carbon::now(),
                                'status' => $orgJson[$orgsStatus[array_search($item, $orgsActs)]] == 1,
                                'answer' => base64_encode(gzcompress(json_encode($requestData), 9)),
                                'expiry_date' => $this->claimService->getExpirationDate(Carbon::now(), 3)
                            ]
                        );
                    }

                    if ($oldNogironAssosatsiya != null) {
                        $oldUser = DB::connection('second_pgsql')->table('user')
                            ->where('id', $oldNogironAssosatsiya->user_id)
                            ->first();

                        $statusOrg = false;
                        if ($oldNogironAssosatsiya->status == 'accepted')
                            $statusOrg = true;

                        $requestData = [
                            "nogiron_match" => $oldNogironAssosatsiya->status == 'accepted' ? 1 : 2,
                            "nogiron_territory" => '',
                            "nogiron_name" => $oldUser->name . ' ' . $oldUser->surname,
                            "nogiron_conclusion_act" => str_replace('<br/>', '', $oldNogironAssosatsiya->comment),
                            "nogiron_datetime" => \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s')
                        ];

                        ClaimOrganizationReview::query()->create(
                            [
                                'claim_id' => $claimModel->id,
                                'monitoring_id' => $monitoring->id,
                                'organization_id' => 18,
                                'answered_at' => ($oldNogironAssosatsiya->status != 'new') ? Carbon::now() : null,
                                'status' => $statusOrg,
                                'answer' => ($oldNogironAssosatsiya->status != 'new') ? base64_encode(gzcompress(json_encode($requestData), 9)) : null,
                                'expiry_date' => $this->claimService->getExpirationDate(Carbon::now(), 3)
                            ]
                        );
                    }
                }

                $claimModel->update(
                    [
                        'status' => $status,
                        'object_id' => $article->id
                    ]
                );

                $response->update(['status' => 2]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();

                $this->output->writeln($e->getMessage());
                $this->output->writeln($e->getTraceAsString());

                $response->update(['status' => 2]);
            }

        }
    }

    private function migrateLatLong()
    {
        $articles = Article::query()->where('lat', '=', '')->whereNotNull('old_id')->get();

        foreach ($articles as $article) {
            $oldObject = DB::connection('third_pgsql')->table('objects')
                ->where('id', $article->old_id)
                ->first();

            if (!$oldObject)
                continue;

            $article->update(
                [
                    'lat' => $oldObject->lat,
                    'long' => $oldObject->long
                ]
            );
        }
    }

    private function migrateBlocks($objectId)
    {
        $article = Article::query()->where('id', $objectId)->first();
        $object = DB::connection('third_pgsql')->table('objects')
            ->where('id', $article->old_id)
            ->first();

        $blocks = DB::connection('third_pgsql')->table('blocks')
            ->where('object_id', $object->id)
            ->whereNull('deleted_at')
            ->get();

        foreach ($blocks as $block) {
            $currentBlock = Block::query()->where(['block_number' => $block->number, 'name' => $block->name, 'article_id' => $objectId])->first();
            if (!$currentBlock) {
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
                $blockModel->status = true;
                $blockModel->appearance_type = null;
                $blockModel->accepted = $block->is_send;
                $blockModel->selected_work_type = false;
                $blockModel->dxa_response_id = null;
                $blockModel->created_at = $block->created_at;
                $blockModel->deleted_at = $block->deleted_at;
                $blockModel->save();
            }
        }
    }

    private function migrateObjectUsers($id)
    {
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

        $article = Article::query()->where('id', $id)->first();
        $users = DB::connection('third_pgsql')->table('participants_construction')
            ->where('object_id', $article->old_id)
            ->where('status', true)
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

        if ($canContinue) {
            foreach ($users as $user) {
                $role = Role::query()->where('old_id', $user->role_id)->first();
                $userDb = User::query()->where('old_id', $user->user_id)->first();
                if ($userDb == null)
                    continue;

                $checkArticleUser = ArticleUser::query()
                    ->where('article_id', $article->id)
                    ->where('user_id', $userDb->id)
                    ->where('role_id', (isset($inspectorRoles[$user->role_id])) ? $inspectorRoles[$user->role_id] : $role->id)
                    ->first();

                if (!$checkArticleUser) {
                    $userRole = new ArticleUser();
                    $userRole->role_id = (isset($inspectorRoles[$user->role_id])) ? $inspectorRoles[$user->role_id] : $role->id;
                    $userRole->article_id = $article->id;
                    $userRole->user_id = $userDb->id;

                    $userRole->save();
                }

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
        }
    }

    private function deletePhaseRegulations()
    {
        $regulations = Regulation::all();
        $count = 0;
        foreach ($regulations as $regulation) {
            $oldRegulation = DB::connection('third_pgsql')->table('regulations')
                ->where('regulation_number', $regulation->regulation_number)
                ->first();
            if ($oldRegulation->phase == '0') {
                $violations = RegulationViolation::query()->where('regulation_id', $regulation->id)->get();
                foreach ($violations as $violation) {
                    Violation::query()->where('id', $violation->violation_id)->delete();
                    $violation->delete();
                }
                ActViolation::query()->where('regulation_id', $regulation->id)->delete();
                $regulation->delete();
                $count++;
            }
        }

        echo 'Deleted phase 0 regulations: ' . $count;
    }

    private function manualMigrateRegulations(string $number)
    {
        $regulation = Regulation::query()->where('regulation_number', $number)->first();
        $oldRegulation = DB::connection('third_pgsql')->table('regulations')
            ->where('regulation_number', $number)
            ->first();
        $violations = DB::connection('third_pgsql')->table('violations')
            ->where('regulation_id', $oldRegulation->id)
            ->where('is_migrated', false)
            ->get();

        $actViolationTypes = [
            '41a6378c-b3f1-453b-bb4f-9538c2309348' => 1,
            '53485e56-899c-4615-bc6e-4ec76a9bfaca' => 2,
            'b8f8f00d-4c3d-48d6-800c-9fffd2394777' => 3,
        ];

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
                'regulation_id' => $regulation->id,
                'violation_id' => $newViolation->id
            ]);

            foreach ($actViolations as $actViolation) {
                $actUser = User::query()->where('old_id', $actViolation->user_id)->first();
                if ($actUser == null)
                    continue;

                $articleUserRole = ArticleUser::query()->where('article_id', $regulation->object_id)->where('user_id', $actUser->id)->first();
                if ($articleUserRole == null)
                    continue;

                $actViolationStatus = ActViolation::PROGRESS;
                if (in_array($regulation->act_status_id, [2, 5, 8, 11, 13]))
                    $actViolationStatus = ActViolation::ACCEPTED;
                if (in_array($regulation->act_status_id, [3, 6, 9, 12]))
                    $actViolationStatus = ActViolation::REJECTED;

                ActViolation::create([
                    'regulation_id' => $regulation->id,
                    'regulation_violation_id' => $newViolation->id,
                    'user_id' => $actUser->id,
                    'act_status_id' => $regulation->act_status_id,
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
    }

    private function syncObjects()
    {
        $objects = DB::connection('third_pgsql')->table('objects')
            ->where('is_migrated', true)
            ->where('region_id', 'c053cdb4-94f6-450f-9da9-f0bf2c145587')
            ->get();

        $objectStatus = [
            'e4bdf226-dae8-46aa-a152-38c4d19889f5' => ObjectStatusEnum::PROGRESS,
            '38a25938-d040-4ccf-9e64-23f483c53e3b' => ObjectStatusEnum::PROGRESS,
            'b50a4eaa-9f68-40ae-83ab-e1971c0ea114' => ObjectStatusEnum::FROZEN,
            'd2fd8089-d7e3-43cc-afe8-9080bf9c0107' => ObjectStatusEnum::SUSPENDED,
            'be3623e7-78f5-48f6-8135-edf3731a838c' => ObjectStatusEnum::SUBMITTED
        ];

        foreach ($objects as $object) {
            $article = Article::query()->where('old_id', $object->id)->first();

            if ($objectStatus[$object->object_status_id] == $article->object_status_id)
                continue;
            else {
                $article->update(
                    [
                        'object_status_id' => $objectStatus[$object->object_status_id]
                    ]
                );
            }
        }
    }

    private function migrateMonitoring()
    {
        $objects = Article::query()
            ->with('users')
            ->where('is_monitoring_get', false)
            ->where('is_regulation_get', true)
            ->whereNotNull('old_id')
            ->limit(50)
            ->get();

        $monitoringTypes = [
            '2c2c6813-bf0b-4ef0-ba70-aefe3001f2f5' => 1,
            'ca41fedc-e165-4ba0-8d4c-bd2865b47799' => 2
        ];

        foreach ($objects as $object) {
            $regulations = DB::connection('third_pgsql')->table('main_regulations')
                ->where('object_id', $object->old_id)
                ->where('is_migrated', false)
                ->get();
            foreach ($regulations as $regulation) {
                $user = User::query()->where('old_id', $regulation->created_by)->first();
                if ($user == null)
                    continue;

                $articleUserRole = ArticleUser::query()->where('article_id', $object->id)->where('user_id', $user->id)->first();
                if ($articleUserRole == null)
                    continue;

                $regulationIds = ($regulation->regulation_ids != null) ? json_decode($regulation->regulation_ids, true) : null;
                $block = null;
                if ($regulationIds != null) {
                    $violation = DB::connection('third_pgsql')->table('violations')
                        ->where('regulation_id', $regulationIds[0])
                        ->first();

                    if ($violation != null) {
                        $blocks = ($violation->blocks != null) ? json_decode($violation->blocks, true) : null;
                        if ($blocks != null) {
                            $objectBlock = DB::connection('third_pgsql')->table('blocks')
                                ->where('id', $blocks[0])
                                ->first();
                            $block = Block::query()->where('article_id', $object->id)->where('name', $objectBlock->name)->first();
                        }
                    }
                }

                $monitoring = Monitoring::create([
                    'object_id' => $object->id,
                    'number' => $regulation->number,
                    'regulation_type_id' => $monitoringTypes[$regulation->regulation_type],
                    'created_at' => $regulation->created_at,
                    'block_id' => ($block != null) ? $block->id : null,
                    'created_by' => $user->id,
                    'created_by_role' => $articleUserRole->role_id,
                ]);

                if ($regulationIds != null) {
                    foreach ($regulationIds as $regulationId) {
                        $reg = DB::connection('third_pgsql')->table('regulations')
                            ->where('id', $regulationId)
                            ->first();

                        if ($reg != null) {
                            $regModel = Regulation::query()->where('object_id', $object->id)->where('regulation_number', $reg->regulation_number)->first();
                            $regModel?->update(
                                [
                                    'monitoring_id' => $monitoring->id
                                ]
                            );
                        }
                    }
                }

                DB::connection('third_pgsql')->table('main_regulations')
                    ->where('id', $regulation->id)
                    ->update(
                        [
                            'is_migrated' => true
                        ]
                    );
            }

            $object->update([
                'is_monitoring_get' => true
            ]);
        }

    }

    private function migrateNewRegulations()
    {
        $regulations = DB::connection('third_pgsql')->table('regulations')
            ->where(DB::raw('phase::int'), '>', 0)
            ->where('is_migrated', false)
            ->where('created_at', '>=', '2024-12-05')
            ->where('deadline', '<', '2025-01-18')
            ->limit(30)
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

        foreach ($regulations as $regulation) {
            $role = Role::query()->where('old_id', $regulation->created_by_role_id)->first();
            $article = Article::query()->where('old_id', $regulation->object_id)->first();
            $user = User::query()->where('old_id', $regulation->created_by)->first();

            if (!$article || $article->object_status_id == ObjectStatusEnum::SUBMITTED)
                continue;

            if ($user == null)
                continue;

            $violations = DB::connection('third_pgsql')->table('violations')
                ->where('regulation_id', $regulation->id)
                ->where('is_migrated', false)
                ->get();

            if ($regulation->phase == null || $regulation->phase == '0')
                continue;

            $regulationStatus = $regulationStatuses[$regulation->phase];
//            if ($regulation->is_administrative && in_array((int)$regulation->phase, [1, 2, 3, 4, 8]))
//                $regulationStatus = RegulationStatusEnum::IN_LAWYER;

            if ($regulation->is_administration_closed && (int)$regulation->phase == 7)
                $regulationStatus = RegulationStatusEnum::LATE_EXECUTION;

            $toUserRoleId = [
                3 => UserRoleEnum::ICHKI,
                2 => UserRoleEnum::MUALLIF,
                1 => UserRoleEnum::TEXNIK
            ];
            $toUserRole = explode('/', $regulation->regulation_number)[1];

            if (User::query()->where('old_id', $regulation->user_id)->first() == null)
                continue;

            $newRegulation = Regulation::create([
                'object_id' => $article->id,
                'deadline' => '2025-02-01',
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
                'lawyer_status_id' => ($regulation->is_administration_closed) ? 3 : null
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

                $regulation_violation = RegulationViolation::create([
                    'regulation_id' => $newRegulation->id,
                    'violation_id' => $newViolation->id
                ]);

                foreach ($actViolations as $actViolation) {
                    $actUser = User::query()->where('old_id', $actViolation->user_id)->first();
                    if ($actUser == null)
                        continue;

                    $articleUserRole = ArticleUser::query()->where('article_id', $article->id)->where('user_id', $actUser->id)->first();
                    if ($articleUserRole == null)
                        continue;

                    $actViolationStatus = ActViolation::PROGRESS;
                    if (in_array($newRegulation->act_status_id, [2, 5, 8, 11, 13]))
                        $actViolationStatus = ActViolation::ACCEPTED;
                    if (in_array($newRegulation->act_status_id, [3, 6, 9, 12]))
                        $actViolationStatus = ActViolation::REJECTED;

                    ActViolation::create([
                        'regulation_id' => $newRegulation->id,
                        'regulation_violation_id' => $regulation_violation->id,
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
    }

    private function migrateRegulations()
    {
        $objects = Article::query()
            ->with('users')
            ->where('is_regulation_get', false)
            ->whereNotNull('old_id')
            ->limit(30)
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
                ->where('is_migrated', false)
                ->get();

            if (!$oldObject)
                continue;

            foreach ($regulations as $regulation) {
                $role = Role::query()->where('old_id', $regulation->created_by_role_id)->first();
                $user = User::query()->where('old_id', $regulation->created_by)->first();

                if (Regulation::query()->where('regulation_number', $regulation->regulation_number)->first() != null)
                    continue;

                if ($user == null)
                    continue;

                $violations = DB::connection('third_pgsql')->table('violations')
                    ->where('regulation_id', $regulation->id)
                    ->where('is_migrated', false)
                    ->get();
                if ($regulation->phase == null || $regulation->phase == '0')
                    continue;

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

                if (User::query()->where('old_id', $regulation->user_id)->first() == null)
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
                        if ($actUser == null)
                            continue;

                        $articleUserRole = ArticleUser::query()->where('article_id', $object->id)->where('user_id', $actUser->id)->first();
                        if ($articleUserRole == null)
                            continue;

                        $actViolationStatus = ActViolation::PROGRESS;
                        if (in_array($newRegulation->act_status_id, [2, 5, 8, 11, 13]))
                            $actViolationStatus = ActViolation::ACCEPTED;
                        if (in_array($newRegulation->act_status_id, [3, 6, 9, 12]))
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

    private function migrateObjects($region_id)
    {
        if ($region_id == null)
            return error_log('Region must be entered!');

        $objects = DB::connection('third_pgsql')->table('objects')
            ->where('is_migrated', false)
            ->where('region_id', $region_id)
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
                ->where('object_id', $object->id)
                ->where('status', true)
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
            $article->task_id = (int)str_replace(' ', '', $object->task_id);
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
            $article->created_at = $object->created_at;
            $article->save();

            Response::query()->updateOrCreate(['task_id' => (int)str_replace(' ', '', $object->task_id)], [
                'module' => ($object->object_type_id == null) ? null : (($objectType[$object->object_type_id] == ObjectTypeEnum::BUILDING) ? 1 : 3),
                'api' => 'my_gov_uz',
                'status' => 0
            ]);

            foreach ($users as $user) {
                $role = Role::query()->where('old_id', $user->role_id)->first();
                $userDb = User::query()->where('old_id', $user->user_id)->first();

                if ($userDb == null)
                    continue;

                $checkArticleUser = ArticleUser::query()
                    ->where('article_id', $article->id)
                    ->where('user_id', $userDb->id)
                    ->where('role_id', (isset($inspectorRoles[$user->role_id])) ? $inspectorRoles[$user->role_id] : $role->id)
                    ->first();

                if (!$checkArticleUser) {
                    $userRole = new ArticleUser();
                    $userRole->role_id = (isset($inspectorRoles[$user->role_id])) ? $inspectorRoles[$user->role_id] : $role->id;
                    $userRole->article_id = $article->id;
                    $userRole->user_id = $userDb->id;
                }

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

            $blocks = DB::connection('third_pgsql')->table('blocks')
                ->where('object_id', $object->id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($blocks as $block) {
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
                $blockModel->status = true;
                $blockModel->appearance_type = null;
                $blockModel->accepted = $block->is_send;
                $blockModel->selected_work_type = false;
                $blockModel->dxa_response_id = null;
                $blockModel->created_at = $block->created_at;
                $blockModel->deleted_at = $block->deleted_at;
                $blockModel->save();
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
        $roleIds = [
            '2064b753-cfbe-4f44-be1e-4d7258110e12',
            'aa84348b-5556-4f67-8cc9-206fb82f3a33',
            'b5392622-4180-4c89-9b4e-2976f05b9150',
            '6126cbeb-b0b8-4059-9758-14ff3c35473f',
            'e7777bfa-7416-44e8-b609-99136ec5d3b0',
            'ebda152a-80b6-4b70-8c72-e70885830276',
            'fdd5d0a8-b2a2-41d1-986a-7748b3a3eefb',
            'bba9a9dd-1d39-400f-8124-a37f24a8bf5c',
            '120e365f-475f-4d4f-a3ce-17d038ee5967',
            '9256f932-738c-4e2e-bcc5-77fe0041a801',
            '853037a6-a4bf-4dd9-95cc-ff3073f01a8f',
            'd16fb793-c015-4d6d-b0c5-a9d33a0f9f52',
            '86ea8778-ce5b-49c6-9693-1f51f31cf80d',
            'e4874137-b63e-40e7-970d-1f16bc6e6ade',
            '2ef59cb1-2ac2-49ca-8f3d-402c714e143a',
            '583806ee-a7c6-45c6-9870-b37542548866',
            '14e463e5-d6cc-40a0-9752-3a1cd57546be',
            '80b740c4-79ef-4c45-a76a-926f90fa3780',
            '2316d2ab-ae0b-497b-9175-642b414c1886',
            'db60cbca-8d2f-4911-9fdc-6fc30102c669'
        ];

        $users = DB::connection('second_pgsql')->table('user')
            ->where('is_migrated', false)
            //->where('active', 1)
            ->whereIn('role_id', $roleIds)
            ->limit(50)
            ->get();

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
