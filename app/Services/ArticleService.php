<?php

namespace App\Services;

use App\DTO\ObjectDto;
use App\Enums\ConstructionWork;
use App\Enums\DifficultyCategoryEnum;
use App\Enums\DxaResponseStatusEnum;
use App\Enums\LogType;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Exceptions\NotFoundException;
use App\Models\Article;
use App\Models\ArticlePaymentLog;
use App\Models\ArticleUser;
use App\Models\Block;
use App\Models\DxaResponse;
use App\Models\FundingSource;
use App\Models\ObjectStatus;
use App\Models\Regulation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEmployee;
use App\Models\UserRole;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\BlockRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ArticleService
{
    protected ObjectDto $objectDto;

    private HistoryService $historyService;
    private HistoryService $objectHistory;

    public function __construct(
        protected ArticleRepositoryInterface $articleRepository,
        protected UserRepositoryInterface $userRepository,
        protected BlockRepositoryInterface $blockRepository,
        protected DxaResponse $dxaResponse,
        protected ImageService  $imageService,
        protected DocumentService  $documentService
    ) {
        $this->historyService = new HistoryService('article_payment_logs');
        $this->objectHistory = new HistoryService('article_histories');
    }

    public function setObjectDto(ObjectDto $objectDto): void
    {
        $this->objectDto = $objectDto;
    }

    public function getObjectById($user, $roleId, $id)
    {
        return $this->getObjects($user, $roleId)->where('articles.id', $id)->first();
    }

    public function findByTaskId($taskId)
    {
        return $this->articleRepository->findByTaskId($taskId);
    }

    public function setObjectUsers($user, $roleId, $id)
    {
        return $this->articleRepository->setObjectUsers($user, $roleId, $id);
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
            case UserRoleEnum::RESPUBLIKA_KUZATUVCHI->value:
            case UserRoleEnum::RESPUBLIKA_BUXGALTER->value:
            case UserRoleEnum::ADMIN->value:
                return Article::query();
            default:
                return Article::query()->whereRaw('1 = 0');
        }
    }

    public function getObjectHistory($id)
    {
        $object = $this->articleRepository->findById($id);
        $histories = $object->histories->map(function ($history) {
            return [
                'id' => $history->id,
                'user' => User::query()->find($history->content->user, ['name', 'surname', 'middle_name']),
                'role' => Role::query()->find($history->content->role, ['name', 'description']),
                'status' => ObjectStatus::query()->find($history->content->status, ['id', 'name']),
                'type' => $history->type,
                'is_change' => LogType::getLabel($history->type),
                'created_at' => $history->created_at,
            ];
        })->sortByDesc('created_at')->values();

        return $histories;

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

    public function rotateUsers($user, $roleId, $firstUserId, $secondUserId)
    {
        $this->articleRepository->rotateUsers($user, $roleId, $firstUserId, $secondUserId);
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

    public function createUser($data)
    {
        $object = Article::query()->find($data->object_id);
        $user = new User();
        $user->name = $data->name;
        $user->middle_name = $data->middle_name;
        $user->surname = $data->surname;
        $user->phone = $data->phone;
        $user->pinfl = $data->pinfl;
        $user->organization_name = $data->organization_name;
        $user->region_id = $data->region_id;
        $user->district_id = $data->district_id;
        $user->identification_number = $data->inn;
        $user->login = $data->pinfl;
        $user->password = $data->pinfl;
        $user->user_status_id = $data->user_status_id;
        $user->image = $this->saveUserImage($data);
        $user->save();

        $this->saveUserFiles($user, $data);
        UserRole::query()->create([
            'user_id' => $user->id,
            'role_id' => $data->role_id
        ]);

        $object->users()->attach($user->id, ['role_id' => $data->role_id]);

        return $user;

    }

    private function saveUserImage($data)
    {
        if ($data->hasFile('image')) {
            return $data->file('image')->store('user', 'public');
        }

        return null;
    }

    private function saveUserFiles(User $user, $data)
    {
        if ($data->hasFile('files')) {
            $user->documents()->delete();
            foreach ($data->file('files') as $file) {
                $path = $file->store('user/docs', 'public');
                $user->documents()->create(['url' => $path]);
            }
        }
    }

    public function calculateTotalPayment($regionId)
    {
        $totalPaid = Article::with('paymentLogs')
            ->where('region_id', $regionId)
            ->where('articles.created_at', '>=' ,  '2024-01-01')
            ->get()
            ->reduce(function ($carry, $article) {
                return $carry + $article->paymentLogs->sum(function ($log) {
                        return isset($log->content->additionalInfo->amount)
                            ? (float)$log->content->additionalInfo->amount
                            : 0;
                    });
            });

        $totalAmount = $this->getArticlesByRegion($regionId)->where('created_at', '>=', '2024-01-01')->get()->sum('price_supervision_service');

        return [
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'notPaid' => $totalAmount - $totalPaid,
        ];
    }


    public function calculatePaymentStatistics($regionId)
    {

        $articles = Article::with('paymentLogs')
            ->where('region_id', $regionId)
            ->get();

        $filteredArticles = $articles->filter(function ($article) {
            return (float) $article->price_supervision_service > 0;
        });

        $results = [
            'all' => $filteredArticles->count(),
            'paid' => 0,
            'partiallyPaid' => 0,
            'notPaid' => 0,
        ];

        foreach ($filteredArticles as $article) {
            $totalPaid = $article->paymentLogs->sum(function ($log) {
                return (float) data_get($log->content, 'additionalInfo.amount', 0);
            });

            $price = (float) $article->price_supervision_service;

            if ($totalPaid >= $price) {
                $results['paid']++;
            } elseif ($totalPaid > 0 && $totalPaid < $price) {
                $results['partiallyPaid']++;
            } else {
                $results['notPaid']++;
            }
        }

        return $results;

    }

    public function changePrice($request, $user, $roleId)
    {
        $object = Article::query()->findOrFail($request->object_id);

        $oldPrice = $object->construction_cost;

        $object->update([
            'construction_cost' => $request->price,
            'price_supervision_service' => price_supervision($request->price)
        ]);

        $meta = [
            'user_id' => $user->id, 'role_id' => $roleId, 'price' => $request->price, 'old_price' => $oldPrice
        ];

        $this->objectHistory->createHistory(
            guId: $object->id,
            status: $object->object_status_id->value,
            type: LogType::ARTICLE_PRICE_HISTORY,
            date: null,
            comment: $item['comment'] ?? "",
            additionalInfo: $meta
        );

    }

    public function attachInspectorObject($user, $roleId, $taskIds, $user_id)
    {
        DB::beginTransaction();
        try {
            $objects = Article::query()->whereIn('task_id', $taskIds)->get();

            foreach ($objects as $object) {
                 $oldInspector = $object->users()->where('role_id', UserRoleEnum::INSPECTOR->value)->first();
                $meta = [
                    'user_id' => $user->id, 'role_id' => $roleId, 'old_user_id' => $oldInspector ? $oldInspector->id : null, 'new_user_id' => $user_id
                ];

                $this->objectHistory->createHistory(
                    guId: $object->id,
                    status: $object->object_status_id->value,
                    type: LogType::ARTICLE_INSPECTOR_HISTORY,
                    date: null,
                    comment: $item['comment'] ?? "",
                    additionalInfo: $meta
                );

                if ($oldInspector)
                {
                    ArticleUser::query()
                        ->where('user_id', $oldInspector->id)
                        ->where('article_id', $object->id)
                        ->where('role_id', UserRoleEnum::INSPECTOR->value)
                        ->update(['role_id' => UserRoleEnum::INSPECTOR->value, 'user_id' => $user_id]);
                }else{
                    $articleUser = new ArticleUser();
                    $articleUser->article_id = $object->id;
                    $articleUser->user_id = $user_id;
                    $articleUser->role_id = UserRoleEnum::INSPECTOR->value;
                    $articleUser->save();
                }




            DB::commit();
            }
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function deletePaymentLog($id, $comment, $user, $roleId)
    {
        DB::beginTransaction();
        try {
            $log = ArticlePaymentLog::query()->findOrFail($id);
            $object = Article::query()->findOrFail($log->gu_id);
            $meta = ['user_id' => $user->id, 'role_id' => $roleId, 'content' => $log->content];

            $this->objectHistory->createHistory(
                guId: $object->id,
                status: $object->object_status_id->value,
                type: LogType::ARTICLE_PRICE_DELETE,
                date: null,
                comment: $comment ?? "",
                additionalInfo: $meta
            );

            $log->delete();
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw  new Exception('Xatolik');
        }
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
    public function createObject($user, $roleId)
    {
        DB::beginTransaction();
        try {
            $response = $this->dxaResponse->find($this->objectDto->responseId);
            $response->update([
                'is_accepted' => true,
                'confirmed_at' => now(),
                'dxa_response_status_id' => DxaResponseStatusEnum::ACCEPTED
            ]);

            if ($response->notification_type==2)
            {
                $oldArticle = Article::query()->where('task_id', $response->old_task_id)->first();
                $this->updateRating($oldArticle, $response);
                $article = $this->saveRepeat($response);
                $this->saveRepeatUser($response, $article);
                $this->saveEmployee($article, false);
                $this->saveHistory($article, $user, $roleId, true);

            }else{
                $article = $this->saveResponse($response);
                $this->saveResponseUser($response, $article);
                $this->saveEmployee($article);
                $this->saveHistory($article, $user, $roleId, false);


            }
            $this->saveBlocks($response, $article);

            $this->acceptResponse($response);

//             $this->sendTax($article);

            DB::commit();

            return $article;

        } catch (\Exception $exception) {
            DB::rollBack();
            throw new NotFoundException($exception->getMessage(), $exception->getLine(), );
        }

    }

    private function saveHistory($article, $user, $roleId, $isUpdate)
    {
        $this->historyService->createHistory(
            guId: $article->id,
            status: $article->object_status_id->value,
            type: $isUpdate ? LogType::ARTICLE_UPDATE_HISTORY : LogType::ARTICLE_CREATE_HISTORY,
            date: null,
            comment: $isUpdate ? 'Obyekt yangilandi' : 'Obyekt yaratildi',
            additionalInfo: [
                'user_id' => $user->id, 'role_id' => $roleId,
            ]
        );
    }

    private function saveRepeat($response)
    {
        $article = Article::query()->where('task_id', $response->old_task_id)->first();
        if (!$article) throw new NotFoundException('Obyekt topilmadi');
        $article->update([
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
            'sphere_id' => $response->sphere_id ?? $article->sphere_id,
            'program_id' => $response->program_id ?? $article->program_id,
            'linear_type' => $response->linear_type,
            'dxa_response_id' => $response->id,
            'price_supervision_service' => price_supervision($response->cost),
            'task_id' => $response->task_id,
            'number_protocol' => $response->number_protocol,
            'positive_opinion_number' => $response->positive_opinion_number,
            'date_protocol' =>$response->date_protocol,
            'funding_source_id' => $response->funding_source_id ?? $article->funding_source_id,
            'org_stir' => $response->application_stir_pinfl ?? $response->pinfl,
            'gnk_id' => $response->gnk_id,
            'reestr_number' => (int)$response->reestr_number,
        ]);
        return $article;
    }

    private function saveResponse($response)
    {
        try {
            $article = new Article();
            $article->name = $response->object_name;
            $article->region_id = $response->region_id;
            $article->district_id = $response->district_id;
            $article->object_status_id = ObjectStatusEnum::PROGRESS;
            $article->object_type_id = $response->object_type_id;
            $article->organization_name = $response->organization_name;
            $article->location_building = $response->location_building;
            $article->address = $response->address;
            $article->cadastral_number = $response->cadastral_number;
            $article->name_expertise = $response->name_expertise;
            $article->difficulty_category_id = DifficultyCategoryEnum::fromString($response->category_object_dictionary);
            $article->construction_cost = $response->cost;
            $article->sphere_id  = $response->sphere_id;
            $article->program_id  = $response->program_id;
            $article->construction_works  = $response->construction_works;
            $article->linear_type  = $response->linear_type;
            $article->appearance_type_id = 1;
            $article->is_accepted = true;
            $article->organization_projects = $response->organization_projects;
            $article->specialists_certificates = $response->specialists_certificates;
            $article->contract_file = $response->contract_file;
            $article->confirming_laboratory = $response->confirming_laboratory;
            $article->file_energy_efficiency = $response->file_energy_efficiency;
            $article->legal_opf = $response->legal_opf;
            $article->lat = $response->lat;
            $article->long = $response->long;
            $article->dxa_response_id = $response->id;
            $article->price_supervision_service = price_supervision($response->cost);
            $article->task_id = $response->task_id;
            $article->number_protocol = $response->number_protocol;
            $article->positive_opinion_number = $response->positive_opinion_number;
            $article->date_protocol = $response->date_protocol;
            $article->funding_source_id = $response->funding_source_id;
            $article->paid = 0;
            $article->payment_deadline = Carbon::now();
            $article->deadline = $response->end_term_work;
            $article->org_stir = $response->application_stir_pinfl ?? $response->pinfl;

            $article->gnk_id = $response->gnk_id;
            $article->reestr_number = (int)$response->reestr_number;

            $article->save();

            return $article;
        }catch (\Exception $exception) {
          throw new $exception;
        }
    }

    private function saveRepeatUser($response, $article)
    {
        foreach ($response->supervisors as $supervisor) {
            $fish = $this->generateFish($supervisor->fish);
            $articleUser = $article->users()->wherePivot('role_id', $supervisor->role_id)->first();
            $user = User::where('pinfl', $supervisor->stir_or_pinfl)->first();


            if ($user) {
                $user->update([
                    'name' => $fish ? $fish[1] : null,
                    'surname' => $fish ? $fish[0] : null,
                    'middle_name' => $fish ? $fish[2] : null,
                    'phone' => $supervisor->phone_number ?? $user->phone,
                    'login' => $supervisor->passport_number ?? $user->login,
                    'organization_name' => $supervisor->organization_name,
                    'password' => bcrypt($supervisor->stir_or_pinfl),
                    'user_status_id' => UserStatusEnum::ACTIVE,
                    'pinfl' => $supervisor->stir_or_pinfl,
                    'identification_number' => $supervisor->identification_number,
                ]);

                if ($articleUser)
                {
                    if ($articleUser->pinfl != $user->pinfl)
                    {
                        ArticleUser::query()
                            ->where('article_id', $article->id)
                            ->where('role_id', $supervisor->role_id)
                            ->where('user_id', $articleUser->id)
                            ->delete();

                        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                        $this->changeRegulations($article->id, $articleUser->id, $user->id, $supervisor->role_id);
                    }
                }else{
                    $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                }

                if (!$user->roles()->where('role_id', $supervisor->role_id)->exists())
                    UserRole::query()->create([
                        'user_id' => $user->id,
                        'role_id' => $supervisor->role_id,
                    ]);
            }else{
                $user = User::create([
                    'name' => $fish ? $fish[1] : null,
                    'surname' => $fish ? $fish[0] : null,
                    'middle_name' => $fish ? $fish[2] : null,
                    'phone' => $supervisor->phone_number,
                    'login' => $supervisor->passport_number ?? $supervisor->stir_or_pinfl,
                    'organization_name' => $supervisor->organization_name,
                    'password' => bcrypt($supervisor->stir_or_pinfl),
                    'user_status_id' => UserStatusEnum::ACTIVE,
                    'pinfl' => $supervisor->stir_or_pinfl,
                    'identification_number' => $supervisor->identification_number,
                ]);
                if ($articleUser)
                {
                    if ($articleUser->pinfl != $user->pinfl)
                    {
                        ArticleUser::query()
                            ->where('article_id', $article->id)
                            ->where('role_id', $supervisor->role_id)
                            ->where('user_id', $articleUser->id)
                            ->delete();

                        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                        $this->changeRegulations($article->id, $articleUser->id, $user->id, $supervisor->role_id);
                    }
                }else{
                    $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                }

                UserRole::query()->create([
                    'user_id' => $user->id,
                    'role_id' => $supervisor->role_id,
                ]);
            }
        }

        $articleInspector = $article->users()->wherePivot('role_id', UserRoleEnum::INSPECTOR->value)->first();
        $inspector = User::query()->find($response->inspector_id);
        if ($articleInspector){
            if (isset($inspector->pinfl) && $articleInspector->pinfl != $inspector->pinfl)
            {
                ArticleUser::query()
                    ->where('article_id', $article->id)
                    ->where('role_id', UserRoleEnum::INSPECTOR->value)
                    ->where('user_id', $articleInspector->id)
                    ->delete();
                $article->users()->attach($inspector->id, ['role_id' => UserRoleEnum::INSPECTOR->value]);
            }
        }else{
            $article->users()->attach($inspector->id, ['role_id' => UserRoleEnum::INSPECTOR->value]);
        }
    }

    private function changeRegulations($objectId, $oldUserId, $newUserId, $roleId)
    {

        Regulation::query()
            ->where('object_id', $objectId)
            ->where('user_id', $oldUserId)
            ->where('role_id', $roleId)
            ->update([
                'user_id' => $newUserId,
                'role_id' => $roleId,
            ]);

        Regulation::query()
            ->where('object_id', $objectId)
            ->where('created_by_user_id', $oldUserId)
            ->where('created_by_role_id', $roleId)
            ->update([
                'created_by_user_id' => $newUserId,
                'created_by_role_id' => $roleId,
            ]);
    }

    private function saveResponseUser($response, $article)
    {
        foreach ($response->supervisors as $supervisor) {
            $fish = $this->generateFish($supervisor->fish);
            $user = User::where('pinfl', $supervisor->stir_or_pinfl)->first();
            if ($user) {
                $user->update([
                    'name' => $fish ? $fish[1] : null,
                    'surname' => $fish ? $fish[0] : null,
                    'middle_name' => $fish ? $fish[2] : null,
                    'phone' => $supervisor->phone_number ?? $user->phone,
                    'login' => $supervisor->passport_number ?? $user->login,
                    'organization_name' => $supervisor->organization_name,
                    'password' => bcrypt($supervisor->stir_or_pinfl),
                    'user_status_id' => UserStatusEnum::ACTIVE,
                    'pinfl' => $supervisor->stir_or_pinfl,
                    'identification_number' => $supervisor->identification_number,
                ]);


                $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                if (!$user->roles()->where('role_id', $supervisor->role_id)->exists())
                    UserRole::query()->create([
                        'user_id' => $user->id,
                        'role_id' => $supervisor->role_id,
                    ]);
            }
            if (!$user) {
                $user = User::create([
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
                ]);
                $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                UserRole::query()->create([
                    'user_id' => $user->id,
                    'role_id' => $supervisor->role_id,
                ]);
            }
        }

       $this->attachInspector($article,  $response->inspector_id);
    }

    public function createObjectManual($taskId)
    {
        DB::beginTransaction();
        try {
            $response = DxaResponse::query()->where('task_id', $taskId)->first();
            if (!$response)  throw new NotFoundException('Ariza topilmadi');
            $article = $this->saveResponse($response);
            $this->saveResponseUser($response, $article);
            $this->saveEmployee($article, false);
            $article->update([
                'created_at' => $response->created_at,
                'deadline' => Carbon::parse($response->created_at)->addYears(2)
            ]);
                DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw new  \Exception($exception->getMessage());
        }
    }

    public function createObjectRegister($request, $user, $roleId)
    {
        DB::beginTransaction();
        try {
            $article = Article::query()->create($request->except('users', 'inspector_id', 'files', 'user_files', 'blocks','expertise_files'));
            $this->attachInspector($article, $request['inspector_id']);
            $this->saveArticleUsers($request['users'], $article);
            $this->saveFiles($request['files'], $request['expertise_files'], $request['user_files'], $article);
            $this->saveBlocksRegister($article, $request['blocks']);
            $this->saveHistory($article, $user, $roleId, false);

            DB::commit();
            return $article;
        }catch (\Exception $exception){
            DB::rollBack();
            throw new  \Exception($exception->getMessage());
        }
    }



    private function attachInspector($article, $inspectorId)
    {
        $article->users()->attach($inspectorId, ['role_id' => UserRoleEnum::INSPECTOR->value]);
    }

    private function saveFiles($files, $expertises, $users, $article)
    {

        $objectFiles = [];
        $expertiseFiles = [];
        $userFiles = [];
        foreach ($files as $file) {
            $path = $file->store('object/files', 'public');
            $objectFiles[] = $path;
        }

        foreach ($expertises as $file) {
            $path = $file->store('object/files', 'public');
            $expertiseFiles[] = $path;
        }

        foreach ($users as $file) {
            $path = $file->store('object/files', 'public');
            $userFiles[] = $path;
        }

        $article->update([
            'files' => json_encode($objectFiles),
            'expertise_files' => json_encode($expertiseFiles),
            'user_files' => json_encode($userFiles),
        ]);
    }

    private function saveArticleUsers($data,$article)
    {
        foreach ($data as $item) {
            $user = User::query()->where('pinfl', $item['pinfl'])->first();
            if ($user)
            {
                $article->users()->attach($user->id, ['role_id' => $item['role_id']]);
                if (!$user->roles()->where('role_id', $item['role_id'])->exists())
                    UserRole::query()->create([
                        'user_id' => $user->id,
                        'role_id' => $item['role_id'],
                    ]);
            }else{
                $user = User::create([
                    'name' => $item['name'] ??  null,
                    'surname' => $item['surname'] ?? null,
                    'middle_name' => $item['middle_name'] ?? null,
                    'phone' => $item['phone'] ?? null,
                    'login' => $item['passport_number'] ?? null,
                    'organization_name' => $item['organization_name'] ?? null,
                    'password' => bcrypt($item['pinfl']),
                    'user_status_id' => UserStatusEnum::ACTIVE,
                    'pinfl' => $item['pinfl'],
                    'identification_number' => $item['pinfl'],
                ]);
                $article->users()->attach($user->id, ['role_id' => $item['role_id']]);
                UserRole::query()->create([
                    'user_id' => $user->id,
                    'role_id' => $item['role_id'],
                ]);
            }
        }
    }

    public function updateObjectManual($taskId)
    {
        DB::beginTransaction();
        try {
            $response = DxaResponse::query()->where('task_id', $taskId)->first();
            if (!$response) throw new NotFoundException('Ariza topilmadi');

            $article = $this->saveRepeat($response);
            $this->saveRepeatUser($response, $article);
            $this->saveEmployee($article, false);
            DB::commit();

        }catch (\Exception $exception){
            DB::rollBack();
            throw new $exception;
        }
    }

    private function generateFish(?string $fish): ?array
    {
        return $fish ? explode(" ", $fish) : null;
    }

    private function saveBlocks(DxaResponse $response, Article $article): void
    {
        $isAccepted = !($response->administrative_status_id == 8);
        if ($response->blocks){
            foreach ($response->blocks as $block) {
                $this->blockRepository->updateBlockByArticle($block->id, $article, $isAccepted);
            }
        }

    }

    private function updateRating($article, $response)
    {
        $ratingConfig = config('app.gasn.rating');
        if (!$ratingConfig) {
            throw new \Exception("Rating config not found.");
        }

        $loyiha = $article->users()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
        $qurilish = $article->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

        $responseLoyiha = $response->supervisors()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
        $responseQurilish = $response->supervisors()->where('role_id', UserRoleEnum::QURILISH->value)->first();

        $loyihaRating = getData($ratingConfig, (int)($responseLoyiha->stir_or_pinfl ?? 0));
        $qurilishRating = getData($ratingConfig, (int)($responseQurilish->stir_or_pinfl ?? 0));

        $data = json_decode($article->rating, true) ?? [];

        $oldLoyihaRating = $data[0]['loyiha'] ?? null;
        $oldQurilishRating = $data[0]['qurilish'] ?? null;

        $rating[0]['loyiha'] =  $loyiha
        ? ($responseLoyiha && $responseLoyiha->stir_or_pinfl != $loyiha->pinfl ? $loyihaRating['data']['data'] ?? null : $oldLoyihaRating)
        : ($responseLoyiha ? $loyihaRating['data']['data'] ?? null : null);

        $rating[0]['qurilish'] = $qurilish
                ? ($responseQurilish && $responseQurilish->stir_or_pinfl != $qurilish->pinfl ? $qurilishRating['data']['data'] ?? null : $oldQurilishRating)
                : ($responseQurilish ? $qurilishRating['data']['data'] ?? null : null);

        $article->update([
            'rating' => json_encode($rating)
        ]);
    }


    private function saveEmployee($article, $create = true): void
    {
        $rating = [];
        $muallif = $article->users()->where('role_id', UserRoleEnum::MUALLIF->value)->first();
        $texnik = $article->users()->where('role_id', UserRoleEnum::TEXNIK->value)->first();
        $buyurtmachi = $article->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
        $loyiha = $article->users()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
        $ichki = $article->users()->where('role_id', UserRoleEnum::ICHKI->value)->first();
        $qurilish = $article->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();


          if ($create)
          {
              $loyihaRating = getData(config('app.gasn.rating'), (int)$loyiha->identification_number);
              $qurilishRating = getData(config('app.gasn.rating'), (int)$qurilish->identification_number);

              $rating[] = [
                  'loyiha' => $loyihaRating['data']['data'] ?? null,
                  'qurilish' => $qurilishRating['data']['data'] ?? null,
              ];

              $article->update([
                  'rating' => json_encode($rating)
              ]);
          }

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

    private function saveBlocksRegister($article, $blocks)
    {
            foreach ($blocks as $blockData) {
                $blockAttributes = [
                    'name' => $blockData['name'],
                    'article_id' => $article->id,
                    'floor' => $blockData['floor'] ?? null,
                    'construction_area' => $blockData['construction_area'] ?? null,
                    'count_apartments' => $blockData['count_apartments'] ?? null,
                    'height' => $blockData['height'] ?? null,
                    'length' => $blockData['length'] ?? null,
                    'block_mode_id' => $blockData['block_mode_id'] ?? null,
                    'block_type_id' => $blockData['block_type_id'] ?? null,
                    'appearance_type' => filter_var($blockData['appearance_type'], FILTER_VALIDATE_BOOLEAN),
                    'created_by' => Auth::id(),
                    'status' => true,
                ];

                Block::create($blockAttributes);
            }
    }



    private function sendTax($object)
    {
        try{
            $authUsername = config('app.mygov.login');
            $authPassword = config('app.mygov.password');
            $customer = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
            $builder = $object->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

            $data = [
                'send_id' => $object->id,
                'send_date' => Carbon::now(),
                'cadastral_number' => $object->cadastral_number,
                'construction_type_name' => $object->construction_works,
                'construction_type_id' => ConstructionWork::fromString($object->construction_works)->value,
                'created_at' => $object->created_at,
                'customer_name' => $customer ? $customer->organization_name : '',
                'district_soato' => $object->district->soato ?? '',
                'general_contractor' => $builder ? $builder->organization_name : '',
                'not_completed_construction' => '',
                'object_name' => $object->name,
                'open_date' => $object->deadline,
                'pinfl_customer' => $customer->name ? $customer->pinfl : '',
                'pinfl_general_contractor' => $builder->name ? $builder->pinfl : '',
                'price_construction_installation' => $object->construction_cost,
                'region_soato' => $object->region->soato,
                'tin_customer' => $customer->name ? '' : $customer->pinfl,
                'tin_general_contractor' => $builder->name ? '' : $builder->pinfl,
            ];

             Http::withBasicAuth($authUsername, $authPassword)->post('https://api.shaffofqurilish.uz/api/v1/constructionSave', $data);
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
        }

    }

    public function acceptResponse(DxaResponse $response)
    {
        try {
                $authUsername = config('app.mygov.login');
                $authPassword = config('app.mygov.password');

                if ($response->object_type_id == 2) {
                    $apiUrl = config('app.mygov.url') . '/update/id/' . $response->task_id . '/action/issue-amount';
                    $formName = 'IssueAmountV4FormNoticeBeginningConstructionWorks';
                } else {
                    $apiUrl = config('app.mygov.linear') . '/update/id/' . $response->task_id . '/action/issue-amount';
                    $formName = 'IssueAmountFormRegistrationStartLinearObject';
                }

                $domain = URL::to('/object-info').'/'.$response->task_id;

                $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));

                $qrImageTag = '<img src="data:image/png;base64,' . $qrImage . '" alt="QR Image" />';

                $return = Http::withBasicAuth($authUsername, $authPassword)
                    ->post($apiUrl, [
                        $formName => [
                            "requisites" => $response->rekvizit->name ?? '',
                            "loacation_rep" => $response->region->name_uz . ' ' . $response->district->name_uz . ' ' . $response->location_building,
                            "name_rep" => $response->organization_name,
                            "amount" => $response->price_supervision_service ?? '',
                            "qr_image" => $qrImageTag,
                            "qr_comment" => "Ushbu QR kod obyekt pasporti hisoblanadi. QR kodni obyektning ko‘rinarli joyiga o‘rnatib qo‘yishingiz talab etiladi"
                        ]
                    ]);

                if ($return->failed()) throw new NotFoundException("mygovda xatolik yuz berdi");
        }catch (\Exception $exception){
            throw new NotFoundException($exception->getMessage(), $exception->getCode());
        }
    }


}
