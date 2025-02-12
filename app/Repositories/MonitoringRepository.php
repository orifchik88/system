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

//                $join->whereExists(function ($query) {
//                    $query->select(DB::raw(1))
//                        ->from('check_list_answers')
//                        ->whereRaw('check_list_answers.monitoring_id = monitorings.id')
//                        ->groupBy('check_list_answers.question_id');
//                });

                if (isset($filters['inspector_id'])) {
                    $join->where('monitorings.created_by', $filters['inspector_id']);
                } elseif (isset($filters['own'])) {
                    $join->where('monitorings.created_by', Auth::user()->id);
                }

                if (isset($filters['year'])) {
                    $join->whereYear('monitorings.created_at', $filters['year']);
                }
                if (isset($filters['month'])) {
                    $join->whereMonth('monitorings.created_at', $filters['month']);
                }
            })
            ->join('articles', function ($join) use ($filters) {
                $join->on('articles.id', '=', 'article_users.article_id')
                    ->whereRaw("
                            articles.created_at <= (to_date(?, 'YYYY-MM') + INTERVAL '1 month - 1 day')
                        ", ["{$filters['year']}-{$filters['month']}"]);
            })
//            ->leftJoinSub($checkListSubquery, 'check_list_summary', function ($join) {
//                $join->on('check_list_summary.monitoring_id', '=', 'monitorings.id');
//            })
            ->when(isset($filters['funding_source_id']), function ($q) use ($filters) {
                $q->where('articles.funding_source_id', $filters['funding_source_id']);
            })
            ->when(isset($filters['region_id']), function ($q) use ($filters) {
                $q->where('articles.region_id', $filters['region_id']);
            })
            ->when(isset($filters['difficulty_category_id']), function ($q) use ($filters) {
                $q->where('articles.difficulty_category_id', $filters['difficulty_category_id']);
            })
            ->when(isset($filters['inspector_id']), function ($q) use ($filters) {
                $q->where('article_users.user_id', $filters['inspector_id']);
            })
            ->when(isset($filters['own']), function ($q) use ($filters) {
                $q->where('article_users.user_id', Auth::user()->id);
            })
            ->when(!isset($filters['own']) && !isset($filters['inspector_id']), function ($q) {
                $q->whereNull('article_users.user_id');
            })
            ->whereIn('articles.object_status_id', [ObjectStatusEnum::PROGRESS, ObjectStatusEnum::FROZEN, ObjectStatusEnum::SUSPENDED])
            ->groupBy('articles.id', 'articles.funding_source_id', 'articles.difficulty_category_id', 'articles.task_id', 'articles.object_status_id')
            ->select([
                'articles.id as object_id',
                'articles.task_id as task_id',
                'articles.object_status_id as status',
                'articles.funding_source_id',
                'articles.difficulty_category_id',
                DB::raw('COUNT(DISTINCT monitorings.id) as count'),
                DB::raw('CASE
                    WHEN COUNT(monitorings.id) > 0 THEN true
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
