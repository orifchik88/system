<?php

namespace App\Repositories;

use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use App\Models\ArticleUser;
use App\Models\Monitoring;
use App\Repositories\Interfaces\MonitoringRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringRepository implements MonitoringRepositoryInterface
{

    public function getMonitoringList(array $filters)
    {
        return ArticleUser::query()
            ->leftJoin('monitorings', function ($join) use ($filters) {
                $join->on('monitorings.object_id', '=', 'article_users.article_id')
                    ->where('monitorings.created_by_role', UserRoleEnum::INSPECTOR);

                // Faqat kerakli foydalanuvchi uchun monitoringlarni cheklash
                if (isset($filters['inspector_id'])) {
                    $join->where('monitorings.created_by', $filters['inspector_id']);
                } elseif (isset($filters['own'])) {
                    $join->where('monitorings.created_by', Auth::user()->id);
                }

                // Filtrlarni JOIN ichida qo‘shamiz
                if (isset($filters['year'])) {
                    $join->whereYear('monitorings.created_at', $filters['year']);
                }
                if (isset($filters['month'])) {
                    $join->whereMonth('monitorings.created_at', $filters['month']);
                }
            })
            ->leftJoin('check_list_answers', 'check_list_answers.monitoring_id', '=', 'monitorings.id')
            ->join('articles', 'articles.id', '=', 'article_users.article_id')
            ->when(isset($filters['funding_source_id']), function ($q) use ($filters) {
                $q->where('articles.funding_source_id', $filters['funding_source_id']);
            })
            ->when(isset($filters['difficulty_category_id']), function ($q) use ($filters) {
                $q->where('articles.difficulty_category_id', $filters['difficulty_category_id']);
            })
            ->where('article_users.user_id', isset($filters['inspector_id']) ? $filters['inspector_id'] : Auth::user()->id)
            ->where('articles.object_status_id', ObjectStatusEnum::PROGRESS)
            ->groupBy('article_users.article_id', 'articles.funding_source_id', 'articles.difficulty_category_id', 'articles.task_id')
            ->select([
                'article_users.article_id as object_id',
                'articles.task_id as task_id',
                'articles.funding_source_id',
                'articles.difficulty_category_id',
                DB::raw('COUNT(monitorings.id) FILTER(where check_list_answers.monitoring_id is not null) as count'),
                DB::raw('CASE
                    WHEN COUNT(monitorings.id) FILTER(where check_list_answers.monitoring_id is not null) > 0 THEN true
                    ELSE false
                END as is_monitored'),
            ])
            ->get();
    }

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
        return Monitoring::query()->with(['regulations', 'checklists', 'regulationType', 'role', 'user'])->whereIn('object_id', $objectIds);
    }

    public function searchMonitorings($query, $filters)
    {
        return
            $query->when(isset($filters['object_name']), function ($q) use ($filters) {
                $q->whereHas('article', function ($query) use ($filters) {
                    $query->where('name', 'like', '%' . $filters['object_name'] . '%');
                });
            })
                ->when(isset($filters['region_id']), function ($q) use ($filters) {
                    $q->whereHas('article', function ($query) use ($filters) {
                        $query->where('region_id', $filters['region_id']);
                    });
                })
                ->when(isset($filters['task_id']), function ($q) use ($filters) {
                    $q->whereHas('article', function ($query) use ($filters) {
                        $query->where('task_id', $filters['task_id']);
                    });
                })
                ->when(isset($filters['district_id']), function ($q) use ($filters) {
                    $q->whereHas('article', function ($query) use ($filters) {
                        $query->where('district_id', $filters['district_id']);
                    });
                })
                ->when(isset($filters['role_id']), function ($q) use ($filters) {
                    $q->where('created_by_role', $filters['role_id']);
                })
                ->when(isset($filters['funding_source']), function ($q) use ($filters) {
                    $q->whereHas('article', function ($query) use ($filters) {
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
                ->when(isset($filters['created_by']), function ($q) use ($filters) {
                    $q->where('created_by_role', $filters['created_by'])->where('created_at', '>=', '2024-12-13 00:00:00');
                })
                ->when(isset($filters['category']), function ($q) use ($filters) {
                    $q->whereHas('article', function ($query) use ($filters) {
                        $query->where('difficulty_category_id', $filters['category']);
                    });
                });
    }

}
