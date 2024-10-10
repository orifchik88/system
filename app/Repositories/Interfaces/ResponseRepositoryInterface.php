<?php

namespace App\Repositories\Interfaces;


use App\Helpers\ModuleType;

interface ResponseRepositoryInterface
{
    public function updateOrCreate(int $module, string $api, int $taskId);

    public function getActiveList(
        int $module
    );

    public function setStatus(
        int $id,
        int $status
    );
}
