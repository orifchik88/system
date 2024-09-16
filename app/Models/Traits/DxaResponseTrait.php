<?php

namespace App\Models\Traits;

use App\Models\DxaResponse;

trait DxaResponseTrait
{
    public static function getResponse($taskId): DxaResponse
    {
        return self::where('task_id', $taskId)->first();
    }

    public function getOldTaskIds($taskId): ?array
    {
        $oldTaskIds = [];

        while ($taskId) {
            $task = self::query()->where('task_id', $taskId)->first();

            if ($task) {
                if ($task->old_task_id) {
                    $oldTask = DxaResponse::query()->where('task_id', $task->old_task_id)->first();
                    $oldTaskIds[] = [
                        'id' => $oldTask->id,
                        'task_id' => $oldTask->task_id,
                        'created_at' => $oldTask->created_at,
                    ];
                }
                $taskId = $task->old_task_id;
            } else {
                break;
            }
        }

        return $oldTaskIds;
    }
}
