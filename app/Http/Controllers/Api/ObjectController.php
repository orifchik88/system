<?php

namespace App\Http\Controllers\Api;

use App\DTO\ObjectDto;
use App\Http\Requests\ObjectRequest;
use App\Http\Resources\FundingSourceResource;
use App\Http\Resources\ObjectSectorResource;
use App\Http\Resources\ObjectTypeResource;
use App\Services\ArticleService;
use Illuminate\Http\FileHelpers;
use Illuminate\Http\JsonResponse;

class ObjectController extends BaseController
{


    public function __construct(
        protected ArticleService $service,
    )
    {
    }

    public function objectTypes(): JsonResponse
    {
        try {
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
            return $this->sendSuccess([], 'Object created');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
