<?php

namespace App\Http\Controllers\Api;

use App\DTO\ObjectDto;
use App\Enums\LogType;
use App\Enums\ObjectCheckEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Requests\ObjectManualRequest;
use App\Http\Requests\ObjectRequest;
use App\Http\Requests\ObjectUserRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\FundingSourceResource;
use App\Http\Resources\UserResource;
use App\Models\Article;
use App\Models\ArticleHistory;
use App\Services\ArticleService;
use App\Services\HistoryService;
use Hamcrest\Core\JavaForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObjectController extends BaseController
{


    public function __construct(
        protected ArticleService $service,
    )
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(): JsonResponse
    {

        try {
            $query = $this->service->getObjects($this->user, $this->roleId);
            $filters = request()->only(['status', 'name', 'customer', 'funding_source', 'object_type', 'task_id', 'region_id', 'district_id', 'user_search']);

            $objects = $this->service->searchObjects($query, $filters)
                ->orderBy('created_at', request('sort_by_date', 'DESC'))
                ->paginate(request('per_page', 10));

            return $this->sendSuccess(ArticleResource::collection($objects), 'Objects retrieved successfully.', pagination($objects));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }

    }

    public function oneTimeUserCreate()
    {
        $this->service->createOneTimeUser(request('task_id'));
        return $this->sendSuccess([], 'Object created successfully.');
    }

    public function rotation(): JsonResponse
    {
        try {
            $this->service->rotateUsers($this->user, $this->roleId, request('user_id'), request('rotation_user_id'));

            return $this->sendSuccess([], 'Rotation completed successfully.');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function objectByParams(): JsonResponse
    {
        try {
            if (!request('task_id') && !request('gnk_id') && !request('expertize_number')) throw new NotFoundHttpException('Object not found');

            $params = request()->only(['task_id', 'gnk_id', 'expertize_number']);
            $object = $this->service->findArticleByParams($params);

            return $this->sendSuccess(ArticleResource::make($object), 'Object retrieved successfully.');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function userObjects(): JsonResponse
    {
        try {
            $user = $this->service->getUserByInnAndRole(request('inn'), UserRoleEnum::QURILISH->value);

            if (!$user) throw new NotFoundHttpException('User not found');

            $objects = $user->objects()->paginate(request('per_page', 10));

            return $this->sendSuccess(ArticleResource::collection($objects), 'Objects retrieved successfully.', pagination($objects));

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function accountObjects(): JsonResponse
    {
        $filters = request()->only(['status', 'name', 'customer', 'funding_source', 'object_type', 'task_id', 'region_id', 'district_id', 'user_search']);

        $query = $this->service->getObjects($this->user, $this->roleId);
        $query = $this->service->getAccountObjectsQuery($query, request('status'));
        $query = $this->service->searchObjects($query, $filters);

        $objects = $query->orderBy('created_at', request('sort_by_date', 'DESC'))
                        ->paginate(\request('perPage', 10));

        return $this->sendSuccess(ArticleResource::collection($objects), 'Objects retrieved successfully.', pagination($objects));
    }

    public function totalPayment(): JsonResponse
    {
        try {
            $result = $this->service->calculateTotalPayment(request('region_id'));
            return $this->sendSuccess($result, 'All Payments');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function paymentStatistics(): JsonResponse
    {
        try {
            $statistics = $this->service->calculatePaymentStatistics(request('region_id'));
            return $this->sendSuccess($statistics, 'Payment Statistics');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getObject($id): JsonResponse
    {
        try {
            $object = $this->service->getObjectById($this->user, $this->roleId, $id);
            return $this->sendSuccess(ArticleResource::make($object), 'Object retrieved successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getObjectByTaskId($taskId): JsonResponse
    {
        try {
            $object = $this->service->findByTaskId($taskId);
            return $this->sendSuccess(ArticleResource::make($object), 'Object retrieved successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function fundingSource(): JsonResponse
    {
        return $this->sendSuccess(FundingSourceResource::collection($this->service->getAllFundingSources()), 'Funding sources');
    }

    public function create(ObjectRequest $request): JsonResponse
    {
        try {
            $dto = new ObjectDto();
            $dto->setResponseId($request->response_id);

            $this->service->setObjectDto($dto);

            $object = $this->service->createObject();
            return $this->sendSuccess(ArticleResource::make($object), 'Object created');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function objectCount(): JsonResponse
    {
        try {
            $data = $this->service->getObjectCount($this->user, $this->roleId);

            return $this->sendSuccess($data, 'Object count retrieved successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function payment(): JsonResponse
    {
        try {

            $this->service->createPayment($this->user, $this->roleId,request('object_id'));

            return $this->sendSuccess([], 'Article retrieved successfully.');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
    public function checkObject()
    {
        try {
            $object = Article::findOrFail(request()->get('id'));

            $missingRoles = $this->checkUsers($object);
//            $blocks = $this->checkBlocks($object);

            if (!empty($missingRoles)) {
                return $this->sendError('Obyekt qatnashchilari yetarli emas ' . implode(', ', $missingRoles));
            }

//            if (!empty($blocks)) {
//                return $this->sendError('Obyekt bloklar foydalanishga topshirilgan ' . implode(', ', $blocks));
//            }

            return $this->sendSuccess(true, 'Article retrieved successfully.');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function manualCreate(ObjectManualRequest $request): JsonResponse
    {
        try {

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    private function checkUsers($object): array
    {
        $users = $object->users;
        $missingRoles = [];
        foreach (ObjectCheckEnum::cases() as $role) {
            $method = $role->value;
            $hasRole = $users->contains(function ($user) use ($method) {
                return $user->{$method}();
            });
            if (!$hasRole) {
                $missingRoles[] = $role->name;
            }
        }
        return $missingRoles;
    }

    public function objectCreateUser(ObjectUserRequest $request): JsonResponse
    {
        try {
            $user = $this->service->createUser($request);
            return $this->sendSuccess(UserResource::make($user), 'Success');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    private function checkBlocks($object): array
    {
        $inactiveBlocks = [];
        foreach ($object->blocks as $block) {
            if (!$block->status){
                $inactiveBlocks[] = $block->name;
            }
        }
        return $inactiveBlocks;
    }


    public function changeObjectStatus(): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->service->getObjectById($this->user, $this->roleId, request('object_id'))->update(['object_status_id' => request('status')]);
            $tableId = (new HistoryService('article_histories'))->createHistory(
                guId: request('object_id'),
                status: request('status'),
                type: LogType::ARTICLE_HISTORY,
                date: null,
                comment: request('comment'),
                additionalInfo: [
                    'user_id' => $this->user->id,
                    'role_id' => $this->roleId,
                ]
            );


//            $history = ArticleHistory::query()->findOrFail($tableId);
//
//            if (request()->hasFile('file'))
//            {
//                $path = $history->store('object/files', 'public');
//                $history->documents()->create(['url' => $path]);
//            }

            DB::commit();
            return $this->sendSuccess(null, 'Object status updated');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

}
