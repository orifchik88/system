<?php

namespace App\Http\Controllers\Api;

use App\DTO\ObjectDto;
use App\Enums\ObjectCheckEnum;
use App\Http\Requests\ObjectRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\FundingSourceResource;
use App\Http\Resources\ObjectSectorResource;
use App\Http\Resources\ObjectStatusResource;
use App\Http\Resources\ObjectTypeResource;
use App\Models\Article;
use App\Models\ObjectStatus;
use App\Services\ArticleService;
use Illuminate\Http\FileHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ObjectController extends BaseController
{


    public function __construct(
        protected ArticleService $service,
    )
    {
    }

    public function index()
    {
        $user = Auth::user();
        if (request()->get('id')) {
            return $this->sendSuccess(ArticleResource::make($user->objects->find(request()->get('id'))), "Object retrieved successfully.");
        }

        $objects = $user->objects()->paginate(\request('perPage', 10));
        return $this->sendSuccess(ArticleResource::collection($objects), 'Objects retrieved successfully.', pagination($objects));
    }

    public function objectTypes(): JsonResponse
    {
        try {
            if (request('id')) {
                return $this->sendSuccess(ObjectTypeResource::make($this->service->getType(request('id'))), 'Object type retrieved successfully.');
            }
            return $this->sendSuccess(ObjectTypeResource::collection($this->service->getAllTypes()), 'Object types');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function objectSectors($id): JsonResponse
    {
        try {
            return $this->sendSuccess(ObjectSectorResource::collection($this->service->getObjectSectors($id)), 'Object sectors');

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
            $dto->setObjectSectorId($request->object_sector_id)
                ->setResponseId($request->response_id)
                ->setFundingSourceId($request->funding_source_id);

            $this->service->setObjectDto($dto);

            $object = $this->service->createObject();
            return $this->sendSuccess($object, 'Object created');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function checkObject()
    {
        try {
            $object = Article::findOrFail(request()->get('id'));

            $missingRoles = $this->checkUsers($object);
            $blocks = $this->checkBlocks($object);

            if (!empty($missingRoles)) {
                return $this->sendError('Obyekt qatnashchilari yetarli emas ' . implode(', ', $missingRoles));
            }

            if (!empty($blocks)) {
                return $this->sendError('Obyekt blocklar foydalanishga topshirilgan ' . implode(', ', $blocks));
            }

            return $this->sendSuccess(ArticleResource::make($object), 'Article retrieved successfully.');

        } catch (\Exception $exception) {
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

    private function checkBlocks($object): array
    {
        $inactiveBlocks = [];
        foreach ($object->articleBlocks as $block) {
            if (!$block->status){
                $inactiveBlocks[] = $block->name;
            }
        }
        return $inactiveBlocks;
    }

    public function status(): JsonResponse
    {
        try {
            if (request('id')) {
                return $this->sendSuccess(ObjectStatusResource::make(ObjectStatus::find(request('id'))), 'Object Status');
            }
            return $this->sendSuccess(ObjectStatusResource::collection(ObjectStatus::all()), 'All Object Statuses');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function changeObjectStatus(): JsonResponse
    {
        try {

             Article::findOrFail(request('object_id'))->update(['object_status_id' => request('status')]);
             return $this->sendSuccess(null, 'Object status updated');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
