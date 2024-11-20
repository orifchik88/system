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
use App\Http\Requests\UserRequest;
use App\Models\Article;
use App\Models\ArticlePaymentLog;
use App\Models\ArticleUser;
use App\Models\DxaResponse;
use App\Models\FundingSource;
use App\Models\Regulation;
use App\Models\User;
use App\Models\UserEmployee;
use App\Models\UserRole;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\BlockRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use PHPUnit\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ArticleService
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
        return $this->getObjects($user, $roleId)->where('articles.id', $id)->first();
    }

    public function findByTaskId($taskId)
    {
        return $this->articleRepository->findByTaskId($taskId);
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
            case UserRoleEnum::ADMIN->value:
                return Article::query();
            default:
                return Article::query()->whereRaw('1 = 0');
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
    public function createObject()
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
                $article = $this->saveRepeat($response);
                $this->saveRepeatUser($response, $article);
            }else{
                $article = $this->saveResponse($response);
                $this->saveResponseUser($response, $article);
                $this->saveBlocks($response, $article);

            }

            $this->acceptResponse($response);
            $this->saveEmployee($article);

            DB::commit();

            return $article;

        } catch (Exception $exception) {
            DB::rollBack();
            throw new NotFoundException($exception->getLine(), $exception->getLine(), );
        }

    }

    private function saveRepeat($response)
    {
        $article = Article::query()->where('task_id', $response->old_task_id)->first();
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
//            'sphere_id' => $response->sphere_id,
//            'program_id' => $response->program_id,
            'linear_type' => $response->linear_type,
            'dxa_response_id' => $response->id,
            'price_supervision_service' => price_supervision($response->cost),
            'task_id' => $response->task_id,
            'number_protocol' => $response->number_protocol,
            'positive_opinion_number' => $response->positive_opinion_number,
            'date_protocol' =>$response->date_protocol,
            'funding_source_id' => $response->funding_source_id,
            //'deadline' => $response->end_term_work,
            'gnk_id' => $response->gnk_id,
            'reestr_number' => (int)$response->reestr_number,
        ]);
        return $article;
    }

    private function saveResponse($response)
    {
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
        $article->gnk_id = $response->gnk_id;
        $article->reestr_number = (int)$response->reestr_number;
        $article->save();

        return $article;
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
                    'phone' => $supervisor->phone_number ?? $user->phone,
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

//        $articleInspector = $article->users()->wherePivot('role_id', UserRoleEnum::INSPECTOR->value)->first();
//        $inspector = User::query()->find($response->inspector_id);
//        if ($articleInspector){
//            if ($articleInspector->pinfl != $inspector->pinfl)
//            {
//                ArticleUser::query()
//                    ->where('article_id', $article->id)
//                    ->where('role_id', UserRoleEnum::INSPECTOR->value)
//                    ->where('user_id', $articleInspector->id)
//                    ->delete();
//                $article->users()->attach($inspector->id, ['role_id' => UserRoleEnum::INSPECTOR->value]);
//            }
//        }else{
//            $article->users()->attach($inspector->id, ['role_id' => UserRoleEnum::INSPECTOR->value]);
//        }
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

        $article->users()->attach($response->inspector_id, ['role_id' => 3]);
    }

    private function generateFish(?string $fish): ?array
    {
        return $fish ? explode(" ", $fish) : null;
    }

    private function saveBlocks(DxaResponse $response, Article $article): void
    {
        $isAccepted = !($response->administrative_status_id == 8 && $response->notification_type == 1);
        foreach ($response->blocks as $block) {
            $this->blockRepository->updateBlockByArticle($block->id, $article, $isAccepted);
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

        $qurilishRating = getData(config('app.gasn.rating'), (int)$qurilish->identification_number);


        $rating[] = [
            'loyiha' => $loyihaRating['data']['data'] ?? null,
            'qurilish' => $qurilishRating['data']['data'] ?? null,
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
                            "amount" => $response->price_supervision_service,
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
