<?php

namespace App\Repositories;

use App\Models\Article;
use App\Models\Monitoring;
use App\Repositories\Interfaces\MonitoringRepositoryInterface;

class MonitoringRepository implements MonitoringRepositoryInterface
{

    public function getMonitoringByUserRole($user, $roleId)
    {
        $objectIds = $user->objects()->where('role_id', $roleId)->pluck('articles.id')->toArray();
        return $this->getMonitoringByObjects($objectIds);
    }

    public function getMonitoringByRegion($regionId)
    {
        $objectIds = Article::query()->where('region_id', $regionId)->pluck('articles.id')->toArray();
        return $this->getMonitoringByObjects($objectIds);
    }

    private function getMonitoringByObjects($objectIds)
    {
        return Monitoring::query()->with(['regulations', 'checklists', 'regulationType'])->whereIn('object_id', $objectIds);
    }

    public function searchMonitorings($query, $filters)
    {
        return
            $query->when(isset($filters['object_name']), function ($q) use($filters) {
                    $q->whereHas('article', function ($query) use($filters) {
                        $query->where('name', 'like', '%' . $filters['object_name'] . '%');
                    });
                })
                ->when(isset($filters['region_id']), function ($q) use($filters) {
                    $q->whereHas('article', function ($query) use($filters) {
                        $query->where('region_id', $filters['region_id']);
                    });
                })
                ->when(isset($filters['task_id']), function ($q) use($filters) {
                    $q->whereHas('article', function ($query) use($filters) {
                        $query->where('task_id', $filters['task_id']);
                    });
                })
                ->when(isset($filters['district_id']), function ($q) use($filters) {
                    $q->whereHas('article', function ($query) use($filters) {
                        $query->where('district_id', $filters['district_id']);
                    });
                })
                ->when(isset($filters['funding_source']), function ($q) use($filters) {
                    $q->whereHas('article', function ($query) use($filters) {
                        $query->where('funding_source_id', $filters['funding_source']);
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
                ->when(isset($filters['created_by']), function ($q) use($filters) {
                    $q->where('created_by_role', $filters['created_by'])->where('created_at', '>=', '2024-12-13 00:00:00');
                })
                ->when(isset($filters['category']), function ($q) use($filters) {
                    $q->whereHas('article', function ($query) use($filters) {
                        $query->where('difficulty_category_id', $filters['category']);
                    });
                });
    }

}
