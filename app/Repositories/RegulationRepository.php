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
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('regulation_status_id', $filters['status']);
            });
    }
}
