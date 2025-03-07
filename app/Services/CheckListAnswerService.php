<?php

namespace App\Services;

use App\Enums\CheckListStatusEnum;
use App\Enums\DxaResponseStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use App\Models\CheckListAnswer;

class CheckListAnswerService
{

    public function getChecklists($user, $roleId, $type)
    {
        switch ($roleId) {
            case UserRoleEnum::INSPECTOR->value:
                return $this->getCheckListByRole($user, $roleId, $type)->whereStatus(CheckListStatusEnum::SECOND)->whereNull('inspector_answered');
            case UserRoleEnum::TEXNIK->value:
            case UserRoleEnum::BUYURTMACHI->value:
                return $this->getCheckListByRole($user, $roleId, $type)->whereStatus(CheckListStatusEnum::FIRST)->whereNotIn('work_type_id',  [1, 11])->whereNull('technic_answered');
            case UserRoleEnum::MUALLIF->value:
            case UserRoleEnum::LOYIHA->value:
                return $this->getCheckListByRole($user, $roleId, $type)->whereStatus(CheckListStatusEnum::FIRST)->whereNotIn('work_type_id',  [1, 11])->whereNull('author_answered');
            case UserRoleEnum::INSPEKSIYA->value:
                return $this->getCheckListByRegion($user, $type)
                    ->whereIn('status', [CheckListStatusEnum::AUTO_CONFIRMED, CheckListStatusEnum::CONFIRMED]);
//                    ->where('inspector_answered', 1);
            case UserRoleEnum::QURILISH_MONTAJ->value:
                return $this->getCheckListByRegion($user, $type)->whereStatus(CheckListStatusEnum::AUTO_CONFIRMED)->where('inspector_answered', 1);
            case UserRoleEnum::HUDUDIY_KUZATUVCHI->value:
                return $this->getCheckListByRegion($user, $type)->whereStatus(CheckListStatusEnum::SECOND)->whereNull('inspector_answered');
            case UserRoleEnum::RESPUBLIKA_KUZATUVCHI->value:
                return CheckListAnswer::query()->whereStatus(CheckListStatusEnum::SECOND)->whereNull('inspector_answered');
            default:
                return CheckListAnswer::query()->whereRaw('1 = 0');
        }
    }

    private function getCheckListByRole($user, $roleId, $type)
    {
        $objectIds = $user->objects()->where('role_id', $roleId)->pluck('article_id');

        return  CheckListAnswer::query()->whereIn('object_id', $objectIds)->whereHas('workType', function ($query) use($type) {
            $query->where('type', $type);
        });
    }

    private function getCheckListByRegion($user, $type){
        $objectIds = Article::query()->where('region_id', $user->region_id)->pluck('id');
        return  CheckListAnswer::query()->whereIn('object_id', $objectIds)->whereHas('workType', function ($query) use($type) {
            $query->where('type', $type);
        });
    }

    public function searchCheckList($query, $filters)
    {
        return $query
            ->join('articles', 'articles.id', '=', 'check_list_answers.object_id')
            ->when(isset($filters['task_id']), function ($query) use ($filters) {
                $query->where('articles.task', $filters['task_id']);
            })
            ->when(isset($filters['start_date']) || isset($filters['end_date']), function ($query) use ($filters) {
                $startDate = isset($filters['start_date']) ? $filters['start_date'] . ' 00:00:00' : null;
                $endDate = isset($filters['end_date']) ? $filters['end_date'] . ' 23:59:59' : null;

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('created_at', '<=', $endDate);
                }
            });
    }


}
