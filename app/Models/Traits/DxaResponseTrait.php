<?php

namespace App\Models\Traits;

use App\Models\Article;
use App\Models\DxaResponse;

trait DxaResponseTrait
{
    public static function getResponse($taskId)
    {
        return self::where('task_id', $taskId)->first() ?? null;
    }

    public function getObject($task_id)
    {
       return Article::query()->where('task_id', $task_id)->first() ?? null;
    }

    public function getOldTaskIds($taskId): ?array
    {
        $oldTaskIds = [];

        while ($taskId) {
            $task = self::query()->where('task_id', $taskId)->first() ?? null;

            if ($task) {
                if ($task->old_task_id) {
                    $oldTask = DxaResponse::query()->where('task_id', $task->old_task_id)->first() ?? null;
                    $oldTaskIds[] = [
                        'id' => $oldTask->id ?? null,
                        'task_id' => $oldTask->task_id ?? null,
                        'created_at' => $oldTask->created_at ?? null,
                    ];
                }
                $taskId = $task->old_task_id ?? null;
            } else {
                break;
            }
        }

        return $oldTaskIds;
    }

    public function scopeSearchByCustomer($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);

        return $query->whereRaw('LOWER(full_name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('LOWER(organization_name) LIKE ?', ['%' . $searchTerm . '%']);

    }
    public function scopeSearchByCustomerName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);

        return $query->whereRaw('LOWER(full_name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('LOWER(organization_name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('LOWER(object_name) LIKE ?', ['%' . $searchTerm . '%']);

    }
}
