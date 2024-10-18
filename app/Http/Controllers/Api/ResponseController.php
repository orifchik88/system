<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ModuleType;
use App\Helpers\ApiType;
use App\Helpers\ResponseTypes;
use App\Http\Controllers\Controller;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;

class ResponseController extends BaseController
{
    private ResponseService $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->middleware('auth');
        parent::__construct();
        $this->responseService = $responseService;
    }

    public function receive(ModuleType $module, ApiType $api): JsonResponse
    {
        $taskId = request()->get('task_id', null);

        if (!$taskId) {
            return $this->sendError("Something went wrong!", [], 500);
        }

        $result = $this->responseService->updateOrCreateResponse(
            module: ResponseTypes::MODULE_TYPE[$module->value],
            api: ResponseTypes::API_TYPE[$api->value],
            taskId: intval($taskId)
        );

        if (!$result) {
            return $this->sendError("Something went wrong!", [], 500);
        }

        return $this->sendSuccess([], 'Saved!');
    }
}
