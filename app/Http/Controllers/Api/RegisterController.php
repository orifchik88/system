<?php

namespace App\Http\Controllers\Api;
use App\Http\Resources\DxaResponseResource;
use App\Models\DxaResponse;
use Illuminate\Http\JsonResponse;

class RegisterController extends BaseController
{
    public function registers(): JsonResponse
    {
        $registers = DxaResponse::query()->get();

        return $this->sendSuccess(DxaResponseResource::collection($registers), 'All registers  successfully.');
    }
}
