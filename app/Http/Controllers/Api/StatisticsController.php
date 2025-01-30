<?php

namespace App\Http\Controllers\Api;

use App\Enums\ObjectStatusEnum;
use App\Enums\RegulationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Exports\ClaimExcel;
use App\Helpers\ClaimStatuses;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticlePalataResource;
use App\Models\Article;
use App\Models\Block;
use App\Models\Claim;
use App\Models\ClaimMonitoring;
use App\Models\District;
use App\Models\Monitoring;
use App\Models\Region;
use App\Models\Regulation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StatisticsController extends BaseController
{

    public function statistics(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('date_from');
            $endDate = $request->get('date_to');

            $regionId = $request->get('region_id');

            $regions = $regionId
                ? District::query()->where('region_id', $regionId)->get(['id', 'name_uz'])
                : Region::all(['id', 'name_uz']);

            $group = $regionId ? 'district_id' : 'region_id';


            $userCounts = User::query()
                ->selectRaw($group . ', COUNT(*) as count')
                ->leftJoin('user_roles', 'user_roles.user_id', '=', 'users.id')
                ->where('user_roles.role_id', 3)
                ->groupBy($group)
                ->pluck('count', $group);

            $articleCounts = $this->getGroupedCounts(Article::query(), $group . ', object_status_id, difficulty_category_id', [$group, 'object_status_id', 'difficulty_category_id'], $startDate, $endDate)->groupBy($group);


            $monitoringCounts = Article::query()
                ->selectRaw($group . ', COUNT(monitorings.id) as count')
                ->leftJoin('monitorings', 'articles.id', '=', 'monitorings.object_id')
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('monitorings.created_at', [$startDate, $endDate]);
                })
                ->when(\request('program_id'), function ($query) {
                    $query->where('articles.program_id', \request('program_id'));
                })
                ->where('created_by_role', UserRoleEnum::INSPECTOR->value)
                ->groupBy('articles.' . $group)
                ->pluck('count', $group);


            $regulationCounts = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, startDate: $startDate, endDate: $endDate);


            $eliminatedRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, status: [6, 8], startDate: $startDate, endDate: $endDate);
            $inProgressRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, status: [1, 2, 3, 4, 5], startDate: $startDate, endDate: $endDate);
            $notExecutionRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, status: [7], startDate: $startDate, endDate: $endDate);

            $costumerRegulationsEliminated = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 6, status: [6, 8], startDate: $startDate, endDate: $endDate);
            $customerInProgressRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 6, status: [1, 2, 3, 4, 5], startDate: $startDate, endDate: $endDate);
            $costumerNotExecutionRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 6, status: [7], startDate: $startDate, endDate: $endDate);

            $manageRegulationsEliminated = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 5, status: [6, 8], startDate: $startDate, endDate: $endDate);
            $manageInProgressRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 5, status: [1, 2, 3, 4, 5], startDate: $startDate, endDate: $endDate);
            $manageNotExecutionRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 5, status: [7], startDate: $startDate, endDate: $endDate);

            $authorRegulationsEliminated = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 7, status: [6, 8], startDate: $startDate, endDate: $endDate);
            $authorInProgressRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 7, status: [1, 2, 3, 4, 5], startDate: $startDate, endDate: $endDate);
            $authorNotExecutionRegulations = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, roleId: 7, status: [7], startDate: $startDate, endDate: $endDate);
            $administratively = $this->getRegulationCounts(relation: 'regulations', groupBy: $group, startDate: $startDate, endDate: $endDate, lawyerStatus: 3);


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
                $costumerNotExecutionRegulations,
                $manageRegulationsEliminated,
                $manageInProgressRegulations,
                $manageNotExecutionRegulations,
                $authorRegulationsEliminated,
                $authorInProgressRegulations,
                $authorNotExecutionRegulations,
                $administratively
            ) {
                $regionId = $region->id;
                $regionArticles = $articleCounts->get($regionId, collect());

                return [
                    'id' => $region->id,
                    'name' => $region->name_uz,
                    'inspector_count' => $userCounts->get($regionId, 0),
                    'object_count' => $regionArticles->sum('count'),
                    'suspended' => $regionArticles->where('object_status_id', ObjectStatusEnum::SUSPENDED)->sum('count'),
                    'submitted' => $regionArticles->where('object_status_id', ObjectStatusEnum::SUBMITTED)->sum('count'),
                    'frozen' => $regionArticles->where('object_status_id', ObjectStatusEnum::FROZEN)->sum('count'),
                    'progress' => $regionArticles->where('object_status_id', ObjectStatusEnum::PROGRESS)->sum('count'),
                    'category_1' => $regionArticles->where('difficulty_category_id', 1)->where('object_status_id', ObjectStatusEnum::PROGRESS)->sum('count'),
                    'category_2' => $regionArticles->where('difficulty_category_id', 2)->where('object_status_id', ObjectStatusEnum::PROGRESS)->sum('count'),
                    'category_3' => $regionArticles->where('difficulty_category_id', 3)->where('object_status_id', ObjectStatusEnum::PROGRESS)->sum('count'),
                    'category_4' => $regionArticles->where('difficulty_category_id', 4)->where('object_status_id', ObjectStatusEnum::PROGRESS)->sum('count'),
                    'monitoring_count' => $monitoringCounts->get($regionId, 0),
                    'regulation_count' => $regulationCounts->get($regionId, 0),
                    'regulation_eliminated' => $eliminatedRegulations->get($regionId, 0),
                    'regulation_progress' => $inProgressRegulations->get($regionId, 0),
                    'regulation_not_execution' => $notExecutionRegulations->get($regionId, 0),
                    'costumer_regulation_eliminated' => $costumerRegulationsEliminated->get($regionId, 0),
                    'customer_regulation_progress' => $customerInProgressRegulations->get($regionId, 0),
                    'customer_regulation_not_execution' => $costumerNotExecutionRegulations->get($regionId, 0),
                    'manage_regulation_eliminated' => $manageRegulationsEliminated->get($regionId, 0),
                    'manage_regulation_progress' => $manageInProgressRegulations->get($regionId, 0),
                    'manage_regulation_not_execution' => $manageNotExecutionRegulations->get($regionId, 0),
                    'author_regulation_eliminated' => $authorRegulationsEliminated->get($regionId, 0),
                    'author_regulation_progress' => $authorInProgressRegulations->get($regionId, 0),
                    'author_regulation_not_execution' => $authorNotExecutionRegulations->get($regionId, 0),
                    'administratively' => $administratively->get($regionId, 0),

                ];
            });

            return $this->sendSuccess($data->values(), 'Data retrieved successfully');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }


    private function getGroupedCounts($query, $selectRaw, $groupBy, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if (\request('program_id')) {
            $query->where('program_id', \request('program_id'));
        }

        return $query
            ->selectRaw("$selectRaw, COUNT(*) as count")
            ->groupBy(...$groupBy)
            ->get();
    }

    private function getRegulationCounts($relation, $groupBy, $roleId = null, $status = [], $startDate = null, $endDate = null, $lawyerStatus = null)
    {
        return Article::query()
            ->selectRaw($groupBy . ', COUNT(' . $relation . '.id) as count')
            ->leftJoin($relation, 'articles.id', '=', $relation . '.object_id')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate, $relation) {
                $query->whereBetween($relation . '.created_at', [$startDate, $endDate]);
            })
            ->when($roleId, function ($query) use ($roleId) {
                $query->where('role_id', $roleId);
            })
            ->when(!empty($status), function ($query) use ($status) {
                $query->whereIn('regulation_status_id', $status);
            })
            ->when($lawyerStatus, function ($query) use ($lawyerStatus) {
                $query->where('lawyer_status_id', $lawyerStatus);
            })
            ->when(\request('program_id'), function ($query) {
                $query->where('articles.program_id', \request('program_id'));
            })
            ->where('created_by_role_id', 3)
            ->groupBy($groupBy)
            ->pluck('count', $groupBy);
    }


    public function reports(Request $request): JsonResponse
    {
        try {
            $columns = $request->input('columns', []);
            $filters = $request->only(['region', 'user', 'date_from', 'date_to', 'object_status', 'inspector']);

            $selectColumns = array_merge(['articles.id'], $columns);

            if (($key = array_search('inspector', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
            }

            if (($key = array_search('sphere', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
                $selectColumns = array_merge(['sphere_id'], $selectColumns);
            }

            if (($key = array_search('blocks', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
            }

            if (($key = array_search('monitorings', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
            }

            if (($key = array_search('status', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
                $selectColumns = array_merge(['object_status_id'], $selectColumns);
            }

            if (($key = array_search('regulations', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
            }

            if (($key = array_search('participants', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
            }

            if (($key = array_search('difficulty_category', $selectColumns)) !== false) {
                unset($selectColumns[$key]);
                $selectColumns = array_merge(['difficulty_category_id'], $selectColumns);
            }


            $query = Article::query()
                ->select($selectColumns)
                ->when(in_array('inspector', $columns), function ($q) use ($columns) {
                    $q->with(['inspector' => function ($query) use ($columns) {
                        $query->select('users.surname', 'users.name', 'users.middle_name', 'users.id as user_id', 'users.phone');
                    }]);
                })
                ->when(in_array('participants', $columns), function ($q) use ($columns) {
                    $q->with(['users' => function ($query) use ($columns) {
                        $query->select('users.surname', 'users.name', 'users.middle_name', 'users.id as user_id', 'users.phone')->whereIn('role_id', [5, 7, 6]);
                    }]);
                })
                ->when(in_array('sphere', $columns), function ($q) {
                    $q->with(['sphere' => function ($query) {
                        $query->select('spheres.name_uz', 'spheres.id as id');
                    }]);
                })
                ->when(in_array('difficulty_category', $columns), function ($q) {
                    $q->with(['difficulty' => function ($query) {
                        $query->select('difficulty_categories.difficulty', 'difficulty_categories.id as id');
                    }]);
                })
                ->when(in_array('regulations', $columns), function ($q) use ($filters) {
                    $q->with(['regulations' => function ($query) use ($filters) {
                        $query->selectRaw('object_id, COUNT(id) as all_count,
							SUM(CASE WHEN regulation_status_id IN (6, 8) AND created_by_role_id = 3 THEN 1 ELSE 0 END) as eliminated_count,
							SUM(CASE WHEN regulation_status_id IN (1, 2, 3, 4, 5)  AND created_by_role_id = 3 THEN 1 ELSE 0 END) as progress_count,
							SUM(CASE WHEN regulation_status_id = 7 AND created_by_role_id = 3 THEN 1 ELSE 0 END) as not_execution_count,
							SUM(CASE WHEN regulation_status_id IN (6, 8) AND role_id = 6  AND created_by_role_id = 3 THEN 1 ELSE 0 END) as costumer_eliminated_count,
							SUM(CASE WHEN regulation_status_id IN (1, 2, 3, 4, 5) AND role_id = 6 AND created_by_role_id = 3 THEN 1 ELSE 0 END) as costumer_progress_count,
							SUM(CASE WHEN regulation_status_id = 7 AND role_id = 6 AND created_by_role_id = 3 THEN 1 ELSE 0 END) as costumer_not_execution_count,
							SUM(CASE WHEN regulation_status_id IN (6, 8) AND role_id = 5  AND created_by_role_id = 3 THEN 1 ELSE 0 END) as manage_eliminated_count,
							SUM(CASE WHEN regulation_status_id IN (1, 2, 3, 4, 5) AND role_id = 5 AND created_by_role_id = 3 THEN 1 ELSE 0 END) as manage_progress_count,
							SUM(CASE WHEN regulation_status_id = 7 AND role_id = 5 AND created_by_role_id = 3 THEN 1 ELSE 0 END) as manage_not_execution_count,
							SUM(CASE WHEN regulation_status_id IN (6, 8) AND role_id = 7  AND created_by_role_id = 3 THEN 1 ELSE 0 END) as author_eliminated_count,
							SUM(CASE WHEN regulation_status_id IN (1, 2, 3, 4, 5) AND role_id = 7 AND created_by_role_id = 3 THEN 1 ELSE 0 END) as author_progress_count,
							SUM(CASE WHEN regulation_status_id = 7 AND role_id = 7  AND created_by_role_id = 3 THEN 1 ELSE 0 END) as author_not_execution_count,
                            SUM(CASE WHEN lawyer_status_id = 3 AND created_by_role_id = 3 THEN 1 ELSE 0 END) as administratively'
                        )
                            ->when(isset($filters['date_from']) && isset($filters['date_to']), function ($q) use ($filters) {
                                return $q->whereBetween('regulations.created_at', [$filters['date_from'], $filters['date_to']]);
                            })
                            ->groupBy('object_id');
                    }]);
                })
                ->when(in_array('status', $columns), function ($q) {
                    $q->with(['objectStatus' => function ($query) {
                        $query->select('object_statuses.name', 'object_statuses.id as id');
                    }]);
                })
                ->when(in_array('blocks', $columns), function ($q) {
                    $q->withCount('blocks');
                })
                ->when(in_array('monitorings', $columns), function ($q) use ($filters) {
                    $q->withCount(['monitorings as monitoring_count' => function ($query) use ($filters) {
                        $query->where('created_by_role', UserRoleEnum::INSPECTOR->value)
                            ->when(isset($filters['date_from']) && isset($filters['date_to']), function ($q) use ($filters) {
                                return $q->whereBetween('monitorings.created_at', [$filters['date_from'], $filters['date_to']]);
                            });
                    }]);
                })
                ->when(isset($filters['region']), function ($q) use ($filters) {
                    return $q->where('region_id', $filters['region']);
                })
                ->when(isset($filters['user']), function ($q) use ($filters) {
                    return $q->whereHas('users', function ($q) use ($filters) {
                        return $q->where('users.id', $filters['user']);
                    });
                })
                ->when(isset($filters['inspector']), function ($q) use ($filters) {
                    $q->whereHas('users', function ($query) use ($filters) {
                        $query->where('user_id', $filters['inspector'])
                            ->where('role_id', UserRoleEnum::INSPECTOR->value);
                    });
                })
                ->when(isset($filters['object_status']), function ($q) use ($filters) {
                    return $q->where('object_status_id', $filters['object_status']);
                });

            $articles = $query->get()->each->setAppends([]);

            return $this->sendSuccess($articles, 'Data retrieved successfully');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function excel()
    {
        ini_set('max_execution_time', 300);
        $filters = request()->only(['region_id', 'start_date', 'end_date']);

        $claims = ClaimMonitoring::query()
            ->join('claims as c', 'c.id', '=', 'claim_monitoring.claim_id')
            ->join('articles as a', 'a.id', '=', 'c.object_id')
            ->join('regions as r', 'r.id', '=', 'a.region_id')
            ->join('districts as d', 'd.id', '=', 'a.district_id')
            ->when(isset($filters['region_id']), fn($q) => $q->where('r.id', $filters['region_id']))
            ->when(isset($filters['start_date']) || isset($filters['end_date']), function ($query) use ($filters) {
                $startDate = $filters['start_date'] ? $filters['start_date'] . ' 00:00:00' : null;
                $endDate = $filters['end_date'] ? $filters['end_date'] . ' 23:59:59' : null;

                if ($startDate && $endDate) {
                    $query->whereBetween('c.created_at', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('c.created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('c.created_at', '<=', $endDate);
                }
            })
            ->where('c.status', ClaimStatuses::TASK_STATUS_CONFIRMED)
            ->whereNotNull('c.object_id')
            ->select([
                'claim_monitoring.*',
                'c.guid as ariza_raqami',
                'a.task_id as obyekt_raqami',
                'a.id as article_id',
                'a.name as obyekt_nomi',
                'r.name_uz as region_name',
                'd.name_uz as district_name',
                'c.end_date as end_date',
            ])
            ->get();

        $articleIds = $claims->pluck('article_id')->unique();
        $blocks = Block::query()
            ->whereIn('article_id', $articleIds)
            ->get()
            ->groupBy('article_id');

        $array = [];
        $count = 1;

        foreach ($claims as $claim) {
            $claimBlocks = $blocks[$claim->article_id] ?? collect();

            $blockCounts = [
                'noturar' => $claimBlocks->where('block_type_id', 1)->count(),
                'turar' => $claimBlocks->whereNotIn('block_type_id', [1, 25])->count(),
                'yakka' => $claimBlocks->where('block_type_id', 25)->count(),
            ];

            $meta = [];
            $countTurar = 0;
            $countNoturar = 0;
            $countYakka = 0;

            $blocksData = json_decode($claim->blocks, true);
            if (is_array($blocksData)) {
                foreach ($blocksData as $item) {
                    $block = $claimBlocks->where('id', $item)->first();
                    if (!$block) continue;

                    if ($block->block_type_id == 1) $countTurar++;
                    elseif ($block->block_type_id == 25) $countYakka++;
                    else $countNoturar++;

                    $meta[] = [
                        'type' => optional($block->type)->name,
                        'count_apartments' => $block->count_apartments,
                    ];
                }
            }

            $operator = base64_decode($claim->operator_answer);
            $uncompressed = @gzuncompress($operator) ?: $operator;
            $areas = json_decode($uncompressed, true);

            if (!$areas) continue;

            $areaSum = $total_area = $total_use_area = $living_area = [
                'total' => 0, 'turar' => 0, 'noturar' => 0, 'yakka' => 0
            ];

            foreach ($areas as $area) {
                $type = match ($area['type']) {
                    1 => 'turar',
                    0 => 'noturar',
                    default => 'yakka',
                };

                $areaSum[$type] += $area['area'];
                $total_area[$type] += $area['total_area'];
                $total_use_area[$type] += $area['total_use_area'];
                $living_area[$type] += $area['living_area'];

                $areaSum['total'] += $area['area'];
                $total_area['total'] += $area['total_area'];
                $total_use_area['total'] += $area['total_use_area'];
                $living_area['total'] += $area['living_area'];
            }

            $tmpArray = [
                'tartib_raqami' => $count,
                'ariza_raqami' => $claim->ariza_raqami,
                'obyekt_nomi' => $claim->obyekt_nomi,
                'obyekt_raqami' => $claim->obyekt_raqami,
                'region_name' => $claim->region_name,
                'district_name' => $claim->district_name,
                'jami_honadon' => (string)$claimBlocks->sum('count_apartments'),
                'jami_block' => (string)$claimBlocks->count(),
                'noturar' => (string)$blockCounts['noturar'],
                'turar' => (string)$blockCounts['turar'],
                'yakka' => (string)$blockCounts['yakka'],
                'count_apartments' => (string)array_sum(array_column($meta, 'count_apartments')),
                'priyomka_jami_block' => (string)count($blocksData),
                'priyomka_noturar' => (string)$countNoturar,
                'priyomka_turar' => (string)$countTurar,
                'priyomka_yakka' => (string)$countYakka,

                'umumiy_maydon' => (string)$total_area['total'],
                'umumiy_noturar' => (string)$total_area['noturar'],
                'umumiy_turar' => (string)$total_area['turar'],
                'umumiy_yakka' => (string)$total_area['yakka'],

                'foydalanish_maydon' => (string)$total_use_area['total'],
                'foydalanish_noturar' => (string)$total_use_area['noturar'],
                'foydalanish_turar' => (string)$total_use_area['turar'],
                'foydalanish_yakka' => (string)$total_use_area['yakka'],

                'yashash_maydon' => (string)$living_area['total'],
                'yashash_noturar' => (string)$living_area['noturar'],
                'yashash_turar' => (string)$living_area['turar'],
                'yashash_yakka' => (string)$living_area['yakka'],

                'qurilish_osti_maydoni' => (string)$areaSum['total'],
                'qurilish_osti_noturar' => (string)$areaSum['noturar'],
                'qurilish_osti_turar' => (string)$areaSum['turar'],
                'qurilish_osti_yakka' => (string)$areaSum['yakka'],
            ];

            $array[] = $tmpArray;
            $count++;
        }

        return Excel::download(
            new ClaimExcel($array),
            'statistic.xls'
        );
    }


}
