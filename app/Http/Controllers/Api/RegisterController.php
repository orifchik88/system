<?php

namespace App\Http\Controllers\Api;
use App\Http\Resources\DxaResponseResource;
use App\Http\Resources\DxaStatusResource;
use App\Models\DxaResponse;
use App\Models\DxaResponseStatus;
use Illuminate\Http\JsonResponse;

class RegisterController extends BaseController
{
    public function registers(): JsonResponse
    {

        if (request()->get('id'))
        {
            $register = DxaResponse::findOrFail(request()->get('id'));
            return $this->sendSuccess(DxaResponseResource::make($register), 'Register successfully.');
        }
        $registers = DxaResponse::query()->get();

        return $this->sendSuccess(DxaResponseResource::collection($registers), 'All registers  successfully.');
    }

    public function status(): JsonResponse
    {
        if (request()->get('id'))
        {
            $register = DxaResponseStatus::findOrFail(request()->get('id'));
            return $this->sendSuccess(DxaStatusResource::make($register), 'Register successfully.');
        }
        $statuses = DxaResponseStatus::query()->get();
        return $this->sendSuccess(DxaStatusResource::collection($statuses), 'All registers status  successfully.');
    }
}
