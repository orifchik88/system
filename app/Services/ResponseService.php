<?php


namespace App\Services;

use App\Helpers\ModuleType;
use App\Helpers\ResponseTypes;
use App\Repositories\Interfaces\ResponseRepository;
use App\Repositories\Interfaces\ResponseRepositoryInterface;

class ResponseService
{
    private ResponseRepositoryInterface $responseRepository;

    public function __construct(
        ResponseRepositoryInterface $responseRepository,
    )
    {
        $this->responseRepository = $responseRepository;
    }

    public function updateOrCreateResponse(int $module, string $api, int $taskId)
    {
        return $this->responseRepository->updateOrCreate(
            module: $module,
            api: $api,
            taskId: $taskId
        );
    }

    public function getActiveResponseList(ModuleType $moduleType) {
        $module = ResponseTypes::MODULE_TYPE[$moduleType->value];

        return $this->responseRepository->getActiveList(
            module: $module
        );
    }

    public function setResponseSuccessStatus(int $id) {
        return $this->responseRepository->setStatus(
            id: $id,
            status: 2
        );
    }

    public function setResponseErrorStatus(int $id) {
        return $this->responseRepository->setStatus(
            id: $id,
            status: 5
        );
    }
}
