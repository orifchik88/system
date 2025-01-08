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
    public function statistisss(): JsonResponse
    {
        try {
            $data = [];
            foreach (Region::all() as $region) {
                $users = User::query()
                    ->where('region_id', 1)
                    ->whereHas('roles', function ($query) {
                        $query->where('role_id', 3);
                    })
                    ->with('roles')
                    ->count();

              $data[] = [
                  'name' => $region->name_uz,
                  'inspector_count' => $users,
                  'object_count' => Article::query()->where('region_id', $region->id)->count(),
                  'suspended' => Article::query()->where('region_id', $region->id)->where('object_status_id', ObjectStatusEnum::SUSPENDED)->count(),
                  'frozen' => Article::query()->where('region_id', $region->id)->where('object_status_id', ObjectStatusEnum::FROZEN)->count(),
                  'progress' => Article::query()->where('region_id', $region->id)->where('object_status_id', ObjectStatusEnum::PROGRESS)->count(),
                  'category_1' => Article::query()->where('region_id', $region->id)->where('object_status_id', ObjectStatusEnum::PROGRESS)->where('difficulty_category_id', 1)->count(),
                  'category_2' => Article::query()->where('region_id', $region->id)->where('object_status_id', ObjectStatusEnum::PROGRESS)->where('difficulty_category_id', 2)->count(),
                  'category_3' => Article::query()->where('region_id', $region->id)->where('object_status_id', ObjectStatusEnum::PROGRESS)->where('difficulty_category_id', 3)->count(),
                  'category_4' => Article::query()->where('region_id', $region->id)->where('object_status_id', ObjectStatusEnum::PROGRESS)->where('difficulty_category_id', 4)->count(),
                  'monitoring_count' =>Article::query()->where('region_id', $region->id)->whereHas('monitorings')->count(),
                  'regulation_count' => Article::query()->where('region_id', $region->id)->whereHas('regulations')->count(),
                  'regulation_eliminated' => Article::query()->where('region_id', $region->id)->whereHas('regulations', function ($query){
                      $query->where('regulation_id', RegulationStatusEnum::ELIMINATED);
                  })->count(),
              ];

            }
            return $this->sendSuccess($data, 'data');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }

    public function statistics(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $regions = Region::all(['id', 'name_uz']);

            $userCounts = User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('role_id', 3);
                })
                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');

            $articlesQuery = Article::query()
                ->select('region_id', 'object_status_id', 'difficulty_category_id')
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });

            $articleCounts = $articlesQuery->clone()
                ->selectRaw('region_id, object_status_id, difficulty_category_id, COUNT(*) as count')
                ->groupBy('region_id', 'object_status_id', 'difficulty_category_id')
                ->get()
                ->groupBy('region_id');


            $monitoringCounts = Article::query()
                ->whereHas('monitorings')
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');


            $regulationCounts = Article::query()
                ->whereHas('regulations')
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');


            $eliminatedRegulations = Article::query()
                ->whereHas('regulations', function ($query) {
                    $query->where('regulation_status_id', RegulationStatusEnum::ELIMINATED);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })

                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');

            $inProgressRegulations = Article::query()
                ->whereHas('regulations', function ($query) {
                    $query->whereNotIn('regulation_status_id', [RegulationStatusEnum::ELIMINATED, RegulationStatusEnum::LATE_EXECUTION, RegulationStatusEnum::IN_LAWYER]);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })

                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');

            $notExecutionRegulations = Article::query()
                ->whereHas('regulations', function ($query) {
                    $query->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })

                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');

            $costumerRegulationsEliminated =  Article::query()
                ->whereHas('regulations', function ($query) {
                    $query->where('regulation_status_id', RegulationStatusEnum::ELIMINATED)
                    ->where('role_id', 6);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })

                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');

            $customerInProgressRegulations = Article::query()
                ->whereHas('regulations', function ($query) {
                    $query->whereNotIn('regulation_status_id', [
                        RegulationStatusEnum::ELIMINATED,
                        RegulationStatusEnum::LATE_EXECUTION,
                        RegulationStatusEnum::IN_LAWYER])
                        ->where('role_id', 6);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })

                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');

            $costumerNotExecutionRegulations  = Article::query()
                ->whereHas('regulations', function ($query) {
                    $query->where('regulation_status_id', RegulationStatusEnum::ELIMINATED)
                        ->where('role_id', 6);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })

                ->selectRaw('region_id, COUNT(*) as count')
                ->groupBy('region_id')
                ->pluck('count', 'region_id');
            

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

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }
}
