<?php

namespace App\Repositories;

use App\Helpers\ModuleType;
use App\Helpers\ResponseTypes;
use App\Models\Response;
use App\Repositories\Interfaces\ResponseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ResponseRepository implements ResponseRepositoryInterface
{
    private Response $model;

    public function __construct(Response $model)
    {
        $this->model = $model;
    }

    public function updateOrCreate(int $module, string $api, int $taskId): Model|Builder
    {
        return $this->model->query()->updateOrCreate(['task_id' => $taskId], [
            'module' => $module,
            'api' => $api,
            'status' => 0
        ]);
    }

    public function getActiveList(int $module): Collection|array
    {
        return $this->model->query()
            ->whereIn('status', [
                0
            ])
            ->where('module', '=', $module)
            ->orderBy('id')
            ->take(20)
            ->get();
    }

    public function setStatus(
        int $id,
        int $status
    ): bool
    {
        return $this->model->query()
            ->where('id', $id)
            ->update([
                'status' => $status
            ]);
    }
}
