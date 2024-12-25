<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Interfaces\RegulationRepositoryInterface;

class RegulationRepository implements RegulationRepositoryInterface
{
    public function getRegulationsByUserRole($user, $roleId)
    {

    }

    public function getRegulationsByObject($user, $roleId)
    {
        $objects = $user->objects()->where('role_id', $roleId)->get();

    }

    public function getRegulationByRegion($regionId)
    {

    }

    public function searchRegulations($query, $filters)
    {
        return $query
            ->when(isset($filters['object_name']), function ($q) use ($filters) {
                $q->whereHas('monitoring.article', function ($query) use ($filters) {
                    $query->where('name', 'like', '%' . $filters['object_name'] . '%');
                });
            })
            ->when(isset($filters['region_id']), function ($q) use ($filters) {
                $q->whereHas('monitoring.article', function ($query) use ($filters) {
                    $query->where('region_id', $filters['region_id']);
                });
            })
            ->when(isset($filters['district_id']), function ($q) use ($filters) {
                $q->whereHas('monitoring.article', function ($query) use ($filters) {
                    $query->where('district_id', $filters['district_id']);
                });
            })
            ->when(isset($filters['organization_name']), function ($q) use ($filters) {
                $q->whereHas('monitoring.article', function ($query) use ($filters) {
                    $query->where('organization_name', 'like', '%' . $filters['organization_name'] . '%');
                });
            })
            ->when(isset($filters['funding_source']), function ($q) use ($filters) {
                $q->whereHas('monitoring.article', function ($query) use ($filters) {
                    $query->where('funding_source_id', $filters['funding_source']);
                });
            })
            ->when(isset($filters['category']), function ($q) use ($filters) {
                $q->whereHas('monitoring.article', function ($query) use ($filters) {
                    $query->where('difficulty_category_id', $filters['category']);
                });
            })
            ->when(isset($filters['task_id']), function ($q) use ($filters) {
                $q->whereHas('object', function ($query) use ($filters) {
                    $query->searchByTaskId($filters['task_id']);
                });
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
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('regulation_status_id', $filters['status']);
            })
            ->when(isset($filters['lawyer_status']), function ($query) use ($filters) {
                $query->where('lawyer_status_id', $filters['lawyer_status']);
            })

            ->when(isset($filters['regulation_number']), function ($query) use ($filters) {
                $query->where('regulation_number', 'like', '%' . $filters['regulation_number'] . '%');
            })
            ->when(isset($filters['created_by_role']), function ($query) use ($filters) {
                $query->where('created_by_role_id', $filters['created_by_role']);
            })
            ->when(isset($filters['deadline_asked']), function ($query) use ($filters) {
                $query->where('deadline_asked', $filters['deadline_asked']);
            });
    }
}
