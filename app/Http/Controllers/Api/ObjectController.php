<?php

namespace App\Http\Controllers\Api;
use App\Http\Resources\ObjectTypeResource;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;

class ObjectController extends BaseController
{

    public function __construct(
        protected ArticleService $service
    ){}
    public function objectTypes(): JsonResponse
    {
        return $this->sendSuccess(ObjectTypeResource::collection($this->service->getAllTypes()), 'Object types');
    }
}
