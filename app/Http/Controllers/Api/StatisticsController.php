<?php

namespace App\Http\Controllers\Api;

use App\Enums\ObjectStatusEnum;
use App\Enums\RegulationStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticlePalataResource;
use App\Models\Article;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends BaseController
{

//    public function statistics(Request $request): JsonResponse
//    {
//        try {
//            $startDate = $request->get('start_date');
//            $endDate = $request->get('end_date');
//
//            $regions = Region::all(['id', 'name_uz']);
//
//            $userCounts = User::query()
//                ->whereHas('roles', function ($query) {
//                    $query->where('role_id', 3);
//                })
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//            $articlesQuery = Article::query()
//                ->select('region_id', 'object_status_id', 'difficulty_category_id')
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                });
//
//            $articleCounts = $articlesQuery->clone()
//                ->selectRaw('region_id, object_status_id, difficulty_category_id, COUNT(*) as count')
//                ->groupBy('region_id', 'object_status_id', 'difficulty_category_id')
//                ->get()
//                ->groupBy('region_id');
//
//
//            $monitoringCounts = Article::query()
//                ->whereHas('monitorings')
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//
//            $regulationCounts = Article::query()
//                ->whereHas('regulations')
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//
//            $eliminatedRegulations = Article::query()
//                ->whereHas('regulations', function ($query) {
//                    $query->where('regulation_status_id', RegulationStatusEnum::ELIMINATED);
//                })
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//            $inProgressRegulations = Article::query()
//                ->whereHas('regulations', function ($query) {
//                    $query->whereNotIn('regulation_status_id', [RegulationStatusEnum::ELIMINATED, RegulationStatusEnum::LATE_EXECUTION, RegulationStatusEnum::IN_LAWYER]);
//                })
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//            $notExecutionRegulations = Article::query()
//                ->whereHas('regulations', function ($query) {
//                    $query->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER);
//                })
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//            $costumerRegulationsEliminated =  Article::query()
//                ->whereHas('regulations', function ($query) {
//                    $query->where('regulation_status_id', RegulationStatusEnum::ELIMINATED)
//                    ->where('role_id', 6);
//                })
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//            $customerInProgressRegulations = Article::query()
//                ->whereHas('regulations', function ($query) {
//                    $query->whereNotIn('regulation_status_id', [
//                        RegulationStatusEnum::ELIMINATED,
//                        RegulationStatusEnum::LATE_EXECUTION,
//                        RegulationStatusEnum::IN_LAWYER])
//                        ->where('role_id', 6);
//                })
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//            $costumerNotExecutionRegulations  = Article::query()
//                ->whereHas('regulations', function ($query) {
//                    $query->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER)
//                        ->where('role_id', 6);
//                })
//                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
//                    $query->whereBetween('created_at', [$startDate, $endDate]);
//                })
//
//                ->selectRaw('region_id, COUNT(*) as count')
//                ->groupBy('region_id')
//                ->pluck('count', 'region_id');
//
//
//            $data = $regions->map(function ($region) use (
//                $userCounts,
//                $articleCounts,
//                $monitoringCounts,
//                $regulationCounts,
//                $eliminatedRegulations,
//                $inProgressRegulations,
//                $notExecutionRegulations,
//                $costumerRegulationsEliminated,
//                $customerInProgressRegulations,
//                $costumerNotExecutionRegulations
//            ) {
//                $regionId = $region->id;
//                $regionArticles = $articleCounts->get($regionId, collect());
//
//                return [
//                    'id' => $region->id,
//                    'name' => $region->name_uz,
//                    'inspector_count' => $userCounts->get($regionId, 0),
//                    'object_count' => $regionArticles->sum('count'),
//                    'suspended' => $regionArticles->where('object_status_id', ObjectStatusEnum::SUSPENDED)->sum('count'),
//                    'frozen' => $regionArticles->where('object_status_id', ObjectStatusEnum::FROZEN)->sum('count'),
//                    'progress' => $regionArticles->where('object_status_id', ObjectStatusEnum::PROGRESS)->sum('count'),
//                    'category_1' => $regionArticles->where('difficulty_category_id', 1)->sum('count'),
//                    'category_2' => $regionArticles->where('difficulty_category_id', 2)->sum('count'),
//                    'category_3' => $regionArticles->where('difficulty_category_id', 3)->sum('count'),
//                    'category_4' => $regionArticles->where('difficulty_category_id', 4)->sum('count'),
//                    'monitoring_count' => $monitoringCounts->get($regionId, 0),
//                    'regulation_count' => $regulationCounts->get($regionId, 0),
//                    'regulation_eliminated' => $eliminatedRegulations->get($regionId, 0),
//                    'regulation_progress' => $inProgressRegulations->get($regionId, 0),
//                    'regulation_not_execution' => $notExecutionRegulations->get($regionId, 0),
//                    'costumer_regulation_eliminated' => $costumerRegulationsEliminated->get($regionId, 0),
//                    'customer_regulation_progress' => $customerInProgressRegulations->get($regionId, 0),
//                    'customer_regulation_not_execution' => $costumerNotExecutionRegulations->get($regionId, 0),
//
//
//                ];
//            });
//
//            return $this->sendSuccess($data->values(), 'Data retrieved successfully');
//
//        }catch (\Exception $exception){
//            return $this->sendError($exception->getMessage(), $exception->getLine());
//        }
//    }
    public function statistics(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $regions = Region::all(['id', 'name_uz']);

            $userCounts = $this->getCounts(User::query(), 'roles', 'role_id', 3, $startDate, $endDate);

            $articleCounts = $this->getGroupedCounts(Article::query(), 'region_id, object_status_id, difficulty_category_id', ['region_id', 'object_status_id', 'difficulty_category_id'], $startDate, $endDate)->groupBy('region_id');

            $monitoringCounts = $this->getCounts(Article::query(), 'monitorings', null, null, $startDate, $endDate);
            $regulationCounts = $this->getCounts(Article::query(), 'regulations', null, null, $startDate, $endDate);

            $eliminatedRegulations = $this->getRegulationCounts(RegulationStatusEnum::ELIMINATED, null, $startDate, $endDate);
            $inProgressRegulations = $this->getRegulationCounts([RegulationStatusEnum::ELIMINATED, RegulationStatusEnum::LATE_EXECUTION, RegulationStatusEnum::IN_LAWYER], 'not_in', $startDate, $endDate);
            $notExecutionRegulations = $this->getRegulationCounts(RegulationStatusEnum::IN_LAWYER, null, $startDate, $endDate);

            $costumerRegulationsEliminated = $this->getRegulationCounts(RegulationStatusEnum::ELIMINATED, null, $startDate, $endDate, 6);
            $customerInProgressRegulations = $this->getRegulationCounts([RegulationStatusEnum::ELIMINATED, RegulationStatusEnum::LATE_EXECUTION, RegulationStatusEnum::IN_LAWYER], 'not_in', $startDate, $endDate, 6);
            $costumerNotExecutionRegulations = $this->getRegulationCounts(RegulationStatusEnum::IN_LAWYER, null, $startDate, $endDate, 6);

            $costumerRegulationsEliminated = $this->getRegulationCounts(RegulationStatusEnum::ELIMINATED, null, $startDate, $endDate, 6);
            $customerInProgressRegulations = $this->getRegulationCounts([RegulationStatusEnum::ELIMINATED, RegulationStatusEnum::LATE_EXECUTION, RegulationStatusEnum::IN_LAWYER], 'not_in', $startDate, $endDate, 6);
            $costumerNotExecutionRegulations = $this->getRegulationCounts(RegulationStatusEnum::IN_LAWYER, null, $startDate, $endDate, 6);

            $costumerRegulationsEliminated = $this->getRegulationCounts(RegulationStatusEnum::ELIMINATED, null, $startDate, $endDate, 6);
            $customerInProgressRegulations = $this->getRegulationCounts([RegulationStatusEnum::ELIMINATED, RegulationStatusEnum::LATE_EXECUTION, RegulationStatusEnum::IN_LAWYER], 'not_in', $startDate, $endDate, 6);
            $costumerNotExecutionRegulations = $this->getRegulationCounts(RegulationStatusEnum::IN_LAWYER, null, $startDate, $endDate, 6);

            $data = $regions->map(function ($region) use (
                $userCounts,
                $articleCounts,
                $monitoringCounts,
                $regulationCounts,
                $eliminatedRegulations,
                $inProgressRegulations,
                $notExecutionRegulations,
                $costumerRegulationsEliminated,
                $customerInProgressRegulations,
                $costumerNotExecutionRegulations
            ) {
                $regionId = $region->id;
                $regionArticles = $articleCounts->get($regionId, collect());

                return [
                    'id' => $region->id,
                    'name' => $region->name_uz,
                    'inspector_count' => $userCounts->get($regionId, 0),
                    'object_count' => $regionArticles->sum('count'),
                    'suspended' => $regionArticles->where('object_status_id', ObjectStatusEnum::SUSPENDED)->sum('count'),
                    'frozen' => $regionArticles->where('object_status_id', ObjectStatusEnum::FROZEN)->sum('count'),
                    'progress' => $regionArticles->where('object_status_id', ObjectStatusEnum::PROGRESS)->sum('count'),
                    'category_1' => $regionArticles->where('difficulty_category_id', 1)->sum('count'),
                    'category_2' => $regionArticles->where('difficulty_category_id', 2)->sum('count'),
                    'category_3' => $regionArticles->where('difficulty_category_id', 3)->sum('count'),
                    'category_4' => $regionArticles->where('difficulty_category_id', 4)->sum('count'),
                    'monitoring_count' => $monitoringCounts->get($regionId, 0),
                    'regulation_count' => $regulationCounts->get($regionId, 0),
                    'regulation_eliminated' => $eliminatedRegulations->get($regionId, 0),
                    'regulation_progress' => $inProgressRegulations->get($regionId, 0),
                    'regulation_not_execution' => $notExecutionRegulations->get($regionId, 0),
                    'costumer_regulation_eliminated' => $costumerRegulationsEliminated->get($regionId, 0),
                    'customer_regulation_progress' => $customerInProgressRegulations->get($regionId, 0),
                    'customer_regulation_not_execution' => $costumerNotExecutionRegulations->get($regionId, 0),
                ];
            });

            return $this->sendSuccess($data->values(), 'Data retrieved successfully');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    private function getCounts($query, $relation = null, $column = null, $value = null, $startDate = null, $endDate = null)
    {
        if ($relation) {
            $query->whereHas($relation, function ($q) use ($column, $value) {
                if ($column && $value) {
                    $q->where($column, $value);
                }
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query
            ->selectRaw('region_id, COUNT(*) as count')
            ->groupBy('region_id')
            ->pluck('count', 'region_id');
    }

    private function getGroupedCounts($query, $selectRaw, $groupBy, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query
            ->selectRaw("$selectRaw, COUNT(*) as count")
            ->groupBy(...$groupBy)
            ->get();
    }

    private function getRegulationCounts($statuses, $type = null, $startDate = null, $endDate = null, $roleId = null)
    {
        $query = Article::query()->whereHas('regulations', function ($q) use ($statuses, $type, $roleId) {
            if ($type === 'not_in') {
                $q->whereNotIn('regulation_status_id', (array)$statuses);
            } else {
                $q->where('regulation_status_id', $statuses);
            }

            if ($roleId) {
                $q->where('role_id', $roleId);
            }
        });

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query
            ->selectRaw('region_id, COUNT(*) as count')
            ->groupBy('region_id')
            ->pluck('count', 'region_id');
    }


}
