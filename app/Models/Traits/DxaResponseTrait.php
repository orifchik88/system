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
                    $oldTaskIds[] = $task->old_task_id;
                }

                $taskId = $task->old_task_id;
            } else {
                break;
            }
        }

        return $oldTaskIds;
    }
}
