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
        return CheckListAnswer::query()
            ->whereHas('object', function ($query) use ($user, $roleId) {
                $query->whereIn('id', $user->roleObjects($roleId)->pluck('article_id'));
            })
            ->whereHas('workType', function ($query) use ($type) {
                $query->where('type', $type);
            });
    }

    private function getCheckListByRegion($user, $type)
    {
        return CheckListAnswer::query()
            ->whereHas('object', function ($query) use ($user) {
                $query->where('region_id', $user->region_id);
            })
            ->whereHas('workType', function ($query) use ($type) {
                $query->where('type', $type);
            });
    }

    public function searchCheckList($query, $filters)
    {
        return $query
            ->when(isset($filters['task_id']), function ($query) use ($filters) {
                $query->whereHas('object', function ($subQuery) use ($filters) {
                    $subQuery->where('task_id', $filters['task_id']);
                });
            })
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                $query->whereHas('object', function ($subQuery) use ($filters) {
                    $subQuery->where('region_id', $filters['region_id']);
                });
            })
            ->when(isset($filters['district_id']), function ($query) use ($filters) {
                $query->whereHas('object', function ($subQuery) use ($filters) {
                    $subQuery->where('district_id', $filters['district_id']);
                });
            })
            ->when(isset($filters['funding_source']), function ($query) use ($filters) {
                $query->whereHas('object', function ($subQuery) use ($filters) {
                    $subQuery->where('funding_source_id', $filters['funding_source']);
                });
            })
            ->when(isset($filters['object_type']), function ($query) use ($filters) {
                $query->whereHas('object', function ($subQuery) use ($filters) {
                    $subQuery->where('object_type_id', $filters['object_type']);
                });
            })
            ->when(isset($filters['start_date']) || isset($filters['end_date']), function ($query) use ($filters) {
                $startDate = $filters['start_date'] ?? null;
                $endDate = $filters['end_date'] ?? null;

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } elseif ($startDate) {
                    $query->where('created_at', '>=', $startDate . ' 00:00:00');
                } elseif ($endDate) {
                    $query->where('created_at', '<=', $endDate . ' 23:59:59');
                }
            });
    }



}
