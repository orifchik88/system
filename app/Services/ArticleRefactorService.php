<?php

namespace App\Services;

use App\DTO\ObjectDto;
use App\Enums\DifficultyCategoryEnum;
use App\Enums\DxaResponseStatusEnum;
use App\Enums\LogType;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Exceptions\NotFoundException;
use App\Models\Article;
use App\Models\ArticlePaymentLog;
use App\Models\DxaResponse;
use App\Models\FundingSource;
use App\Models\UserEmployee;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\BlockRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ArticleRefactorService
{
    protected ObjectDto $objectDto;
    private HistoryService $historyService;

    public function __construct(
        protected ArticleRepositoryInterface $articleRepository,
        protected UserRepositoryInterface $userRepository,
        protected BlockRepositoryInterface $blockRepository,
        protected DxaResponse $dxaResponse,
        protected ImageService  $imageService,
        protected DocumentService  $documentService,
    ) {
        $this->historyService = new HistoryService('article_payment_logs');
    }

    public function setObjectDto(ObjectDto $objectDto): void
    {
        $this->objectDto = $objectDto;
    }

    public function getObjectById($user, $roleId, $id)
    {
        return $this->getObjects($user, $roleId)->where('id', $id)->first();
    }

    public function getObjects($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::INSPECTOR->value:
            case UserRoleEnum::ICHKI->value:
            case UserRoleEnum::MUALLIF->value:
            case UserRoleEnum::TEXNIK->value:
            case UserRoleEnum::LOYIHA->value:
            case UserRoleEnum::BUYURTMACHI->value:
            case UserRoleEnum::QURILISH->value:
                return $this->getArticlesByUserRole($user, $roleId);
            case UserRoleEnum::REGISTRATOR->value:
            case UserRoleEnum::OPERATOR->value:
            case UserRoleEnum::INSPEKSIYA->value:
            case UserRoleEnum::HUDUDIY_KUZATUVCHI->value:
            case UserRoleEnum::QURILISH_MONTAJ->value:
            case UserRoleEnum::BUXGALTER->value:
            case UserRoleEnum::REGKADR->value:
            case UserRoleEnum::YURIST->value:
                return $this->getArticlesByRegion($user->region_id);
            case UserRoleEnum::RESKADR->value:
                return Article::query();
            default:
                return [];
        }
    }

    public function getArticlesByUserRole($user, $roleId)
    {
        return $this->articleRepository->getArticlesByUserRole($user, $roleId);
    }

    public function getArticlesByRegion($regionId)
    {
        return $this->articleRepository->getArticlesByRegion($regionId);
    }

    public function searchObjects($query, $filters)
    {
        return $this->articleRepository->searchObjects($query, $filters);
    }

    public function rotateUsers($firstUserId, $secondUserId)
    {
        $this->articleRepository->rotateUsers($firstUserId, $secondUserId);
    }

    public function getAllFundingSources(): object
    {
        return FundingSource::all();
    }

    public function findArticleByParams($params)
    {
        return $this->articleRepository->findArticleByParams($params);
    }

    public function getUserByInnAndRole($inn, $role)
    {
        return $this->articleRepository->getUserByInnAndRole($inn, $role);
    }

    public function calculateTotalPayment($regionId)
    {
        $totalPaid = Article::with('paymentLogs')
            ->where('region_id', $regionId)
            ->get()
            ->reduce(function ($carry, $article) {
                return $carry + $article->paymentLogs->sum(function ($log) {
                        return isset($log->content->additionalInfo->amount)
                            ? (float)$log->content->additionalInfo->amount
                            : 0;
                    });
            });

        $totalAmount = $this->getArticlesByRegion($regionId)->get()->sum('price_supervision_service');

        return [
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'notPaid' => $totalAmount - $totalPaid,
        ];
    }
    public function calculatePaymentStatistics($regionId)
    {
        $articles = Article::with('paymentLogs')->where('region_id', $regionId)->get();

        return [
            'all' => $articles->count(),
            'paid' => $articles->filter(function ($article) {
                $totalPaid = $article->paymentLogs->sum('content->additionalInfo->amount');
                return $totalPaid >= $article->price_supervision_service;
            })->count(),
            'partiallyPaid' => $articles->filter(function ($article) {
                $totalPaid = $article->paymentLogs->sum('content->additionalInfo->amount');
                return $totalPaid < $article->price_supervision_service && $totalPaid > 0;
            })->count(),
            'notPaid' => $articles->filter(function ($article) {
                $totalPaid = $article->paymentLogs->sum('content->additionalInfo->amount');
                return $totalPaid == 0;
            })->count(),
        ];
    }

    public function createPayment($user, $roleId, $objectId)
    {
        $object = $this->getObjectById($user, $roleId,$objectId);

        $paid = $object->paymentLogs()
            ->get()
            ->sum(function ($log) {
                return $log->content->additionalInfo->amount ?? 0;
            });

        $cost = (float)$object->price_supervision_service - (request('amount') + $paid);


        $meta = ['amount' => request('amount'), 'cost' => $cost];

        $tableId = $this->historyService->createHistory(
            guId: $object->id,
            status: $object->object_status_id->value,
            type: LogType::TASK_HISTORY,
            date: null,
            comment: $item['comment'] ?? "",
            additionalInfo: $meta
        );

        $log = ArticlePaymentLog::query()->findOrFail($tableId);

        if (request()->hasFile('file')) {
            $this->documentService->saveFile($log, 'payment-log',request()->file('file'));
        }

        if (request()->hasFile('image')) {
            $this->imageService->saveImage($log, 'payment-log',request()->file('image'));
        }
    }

    public function getObjectCount($user, $roleId)
    {
        $query = $this->getObjects($user, $roleId);
        return [
            'all' => $query->clone()->count(),
            'progress' => $query->clone()->where('object_status_id', ObjectStatusEnum::PROGRESS)->count(),
            'frozen' => $query->clone()->where('object_status_id', ObjectStatusEnum::FROZEN)->count(),
            'suspended' => $query->clone()->where('object_status_id', ObjectStatusEnum::SUSPENDED)->count(),
            'submitted' => $query->clone()->where('object_status_id', ObjectStatusEnum::SUBMITTED)->count(),
        ];
    }

    public function getAccountObjectsQuery($query, $status)
    {
        return $this->articleRepository->getAccountObjectsQuery($query, $status);
    }


    public function createObject(): Article
    {
        DB::beginTransaction();
        try {
            $response = $this->findResponse();
            $this->updateResponse($response);

            $article = $this->handleArticleCreationOrUpdate($response);
            $this->handleArticleSupervisors($article, $response);
            $this->attachInspector($article, $response->inspector_id);

            $this->saveBlocks($response, $article);


            $this->saveEmployee($article);

            $this->acceptResponse($response);

            DB::commit();
            return $article;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception("Error creating object: " . $exception->getMessage());
        }
    }

    private function findResponse(): DxaResponse
    {
        return $this->dxaResponse->findOrFail($this->objectDto->responseId);
    }

    private function updateResponse(DxaResponse $response): void
    {
        $response->update([
            'is_accepted' => true,
            'confirmed_at' => now(),
            'dxa_response_status_id' => DxaResponseStatusEnum::ACCEPTED
        ]);
    }

    private function handleArticleCreationOrUpdate(DxaResponse $response): Article
    {
        if ($response->notification_type == 2) {
            return $this->updateArticle($response);
        }
        return $this->createNewArticle($response);
    }

    private function updateArticle(DxaResponse $response): Article
    {
        $article = $this->articleRepository->findByTaskId($response->old_task_id);
        $this->articleRepository->update($article, $this->mapArticleData($response, true));
        return $article;
    }

    private function createNewArticle(DxaResponse $response): Article
    {
        return $this->articleRepository->create($this->mapArticleData($response));
    }

    private function handleArticleSupervisors(Article $article, DxaResponse $response): void
    {
        if ($response->notification_type == 2) {
            $article->users()->detach();
        }
        foreach ($response->supervisors as $supervisor) {
            $this->processSupervisor($supervisor, $article);
        }
    }

    private function processSupervisor($supervisor, Article $article): void
    {
        $fish = $this->generateFish($supervisor->fish);
        $userData = $this->mapUserData($supervisor, $fish);
        $user = $this->userRepository->createOrUpdate($userData);

        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
        $this->userRepository->attachRole($user, $supervisor->role_id);
    }

    private function generateFish(?string $fish): ?array
    {
        return $fish ? explode(" ", $fish) : null;
    }

    private function mapArticleData(DxaResponse $response, bool $isUpdate = false): array
    {
        $data =  [
            'name' => $response->object_name,
            'region_id' => $response->region_id,
            'district_id' => $response->district_id,
            'object_type_id' => $response->object_type_id,
            'organization_name' => $response->organization_name,
            'location_building' => $response->location_building,
            'address' => $response->address,
            'construction_works' =>$response->construction_works,
            'cadastral_number' => $response->cadastral_number,
            'name_expertise' => $response->name_expertise,
            'difficulty_category_id' => DifficultyCategoryEnum::fromString($response->category_object_dictionary),
            'construction_cost' => $response->cost,
            'organization_projects' => $response->organization_projects,
            'specialists_certificates' => $response->specialists_certificates,
            'contract_file' => $response->contract_file,
            'confirming_laboratory' => $response->confirming_laboratory,
            'file_energy_efficiency' => $response->file_energy_efficiency,
            'legal_opf' => $response->legal_opf,
            'lat' => $response->lat,
            'long' => $response->long,
            'sphere_id' => $response->sphere_id,
            'program_id' => $response->program_id,
            'linear_type' => $response->linear_type,
            'dxa_response_id' => $response->id,
            'price_supervision_service' => price_supervision($response->cost),
            'task_id' => $response->task_id,
            'number_protocol' => $response->number_protocol,
            'positive_opinion_number' => $response->positive_opinion_number,
            'date_protocol' =>$response->date_protocol,
            'funding_source_id' => $response->funding_source_id,
            'deadline' => $response->end_term_work,
            'gnk_id' => $response->gnk_id,
            'reestr_number' => (int)$response->reestr_number,
            'appearance_type_id' => 1
        ];
        if ($isUpdate) {
            unset($data['object_status_id']);
        } else {
            $data['object_status_id'] = ObjectStatusEnum::PROGRESS;
        }

        return $data;

    }

    private function mapUserData($supervisor, $fish): array
    {
        return [
            'name' => $fish ? $fish[1] : null,
            'surname' => $fish ? $fish[0] : null,
            'middle_name' => $fish ? $fish[2] : null,
            'phone' => $supervisor->phone_number,
            'login' => $supervisor->passport_number,
            'organization_name' => $supervisor->organization_name,
            'password' => bcrypt($supervisor->stir_or_pinfl),
            'user_status_id' => UserStatusEnum::ACTIVE,
            'pinfl' => $supervisor->stir_or_pinfl,
            'identification_number' => $supervisor->identification_number,
        ];
    }

    private function attachInspector(Article $article, $inspectorId): void
    {
        $inspector = $this->userRepository->findByPinfl($inspectorId);
        if ($inspector) {
            $article->users()->attach($inspector->id, ['role_id' => UserRoleEnum::INSPECTOR->value]);
        }
    }

    private function saveBlocks(DxaResponse $response, Article $article): void
    {
        foreach ($response->blocks as $block) {
           $this->blockRepository->updateBlockByArticle($block->id, $article);
        }
    }

    private function saveEmployee(Article $article): void
    {
        $rating = [];
        $muallif = $article->users()->where('role_id', UserRoleEnum::MUALLIF->value)->first();
        $texnik = $article->users()->where('role_id', UserRoleEnum::TEXNIK->value)->first();
        $buyurtmachi = $article->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
        $loyiha = $article->users()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
        $ichki = $article->users()->where('role_id', UserRoleEnum::ICHKI->value)->first();
        $qurilish = $article->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

        $loyihaRating = getData(config('app.gasn.rating'), (int)$loyiha->identification_number);
        if (!$loyihaRating) throw new NotFoundException('Loyiha tashkilotining reytinggini olishda muammo');

        $qurilishRating = getData(config('app.gasn.rating'), (int)$qurilish->identification_number);

        if (!$qurilishRating) throw new NotFoundException('Qurilish tashkilotining reytinggini olishda muammo');

        $rating[] = [
            'loyiha' => $loyihaRating['data']['data'],
            'qurilish' => $qurilishRating['data']['data'],
        ];

        $article->update([
            'rating' => json_encode($rating)
        ]);

        if (!UserEmployee::query()->where('user_id', $texnik->id)->where('parent_id', $buyurtmachi->id)->exists()) {
            UserEmployee::query()->create([
                'user_id' => $texnik->id,
                'parent_id' => $buyurtmachi->id,
            ]);
        }

        if (!UserEmployee::query()->where('user_id', $muallif->id)->where('parent_id', $loyiha->id)->exists()) {
            UserEmployee::query()->create([
                'user_id' => $muallif->id,
                'parent_id' => $loyiha->id,
            ]);
        }

        if (!UserEmployee::query()->where('user_id', $ichki->id)->where('parent_id', $qurilish->id)->exists()) {
            UserEmployee::query()->create([
                'user_id' => $ichki->id,
                'parent_id' => $qurilish->id,
            ]);
        }

    }

    private function acceptResponse(DxaResponse $response)
    {
        try {
            if (env('APP_ENV') === 'development') {
                $authUsername = config('app.mygov.login');
                $authPassword = config('app.mygov.password');

                if ($response->object_type_id == 2) {
                    $apiUrl = config('app.mygov.url') . '/update/id/' . $response->task_id . '/action/issue-amount';
                    $formName = 'IssueAmountV4FormNoticeBeginningConstructionWorks';
                } else {
                    $apiUrl = config('app.mygov.linear') . '/update/id/' . $response->task_id . '/action/issue-amount';
                    $formName = 'IssueAmountFormRegistrationStartLinearObject';
                }

                $return = Http::withBasicAuth($authUsername, $authPassword)
                    ->post($apiUrl, [
                        $formName => [
                            "requisites" => $response->rekvizit->name,
                            "loacation_rep" => $response->region->name_uz . ' ' . $response->district->name_uz . ' ' . $response->location_building,
                            "name_rep" => $response->organization_name,
                            "amount" => $response->price_supervision_service
                        ]
                    ]);

                if ($return->failed()) throw new NotFoundException($return->reason());
            }

        }catch (\Exception $exception){
            throw new NotFoundException($exception->getMessage(), $exception->getCode());
        }

    }


}
