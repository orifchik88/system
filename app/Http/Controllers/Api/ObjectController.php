<?php

namespace App\Http\Controllers\Api;

use App\DTO\ObjectDto;
use App\Enums\LogType;
use App\Enums\ObjectCheckEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Requests\ObjectRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\FundingSourceResource;
use App\Http\Resources\ObjectSectorResource;
use App\Http\Resources\ObjectStatusResource;
use App\Http\Resources\ObjectTypeResource;
use App\Models\Article;
use App\Models\ArticlePaymentLog;
use App\Models\ArticleUser;
use App\Models\ObjectStatus;
use App\Models\User;
use App\Models\UserRole;
use App\Services\ArticleRefactorService;
use App\Services\ArticleService;
use App\Services\HistoryService;
use Illuminate\Http\FileHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObjectController extends BaseController
{

    private HistoryService $historyService;

    public function __construct(
        protected ArticleRefactorService $service,
    )
    {
        $this->middleware('auth');
        parent::__construct();
        $this->historyService = new HistoryService('article_payment_logs');
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

    public function rotation(): JsonResponse
    {
        try {
            $this->service->rotateUsers(request('user_id'), request('rotation_user_id'));

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
            $object = Article::query()->with('paymentLogs')->findOrFail(request('object_id'));

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
                $file = request()->file('file');
                $path = $file->store('document/payment-log', 'public');
                $log->documents()->create(['url' => $path]);
            }

            if (request()->hasFile('image')) {
                $file = request()->file('image');
                $path = $file->store('images/payment-log', 'public');
                $log->images()->create(['url' => $path]);
            }

            return $this->sendSuccess([], 'Article retrieved successfully.');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function changeObjectStatus(): JsonResponse
    {
        try {

            Article::findOrFail(request('object_id'))->update(['object_status_id' => request('status')]);
            return $this->sendSuccess(null, 'Object status updated');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
