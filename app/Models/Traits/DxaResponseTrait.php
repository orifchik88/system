<?php

namespace App\Models\Traits;

use App\Models\DxaResponse;

trait DxaResponseTrait
{
    public static function getResponse($taskId)
    {
        return self::where('task_id', $taskId)->first() ?? null;
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
}
