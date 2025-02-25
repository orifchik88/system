<?php

namespace App\Repositories;

use App\Enums\DxaResponseStatusEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use App\Models\ArticleUser;
use App\Models\DxaResponse;
use App\Models\ObjectUserHistory;
use App\Models\Regulation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\InspectorNotification;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Services\MessageTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function create(array $data): Article
    {
        $article = new Article();
        $article->fill($data);
        $article->save();
        return $article;
    }

    public function update(Article $article, array $data): bool
    {
        return $article->update($data);
    }

    public function getList($filters)
    {
        return Article::query()
            ->with([
                'region',
                'district',
                'users',
                'difficulty',
                'objectType',
                'sphere',
                'fundingSource',
            ])
            ->when(!empty($filters['region_id']), function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            })
            ->when(!empty($filters['date_from']) || !empty($filters['date_to']), function ($query) use ($filters) {
                $startDate = !empty($filters['date_from']) ? $filters['date_from'] . ' 00:00:00' : null;
                $endDate = !empty($filters['date_to']) ? $filters['date_to'] . ' 23:59:59' : null;

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('created_at', '<=', $endDate);
                }
            })->paginate($filters['per_page'] ?? 50);
    }
    public function getListPalata($filters)
    {
        return Article::query()
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                $query->where('articles.region_id', $filters['region_id']);
            })
            ->when(isset($filters['date_from']) || isset($filters['date_to']), function ($query) use ($filters) {
                $startDate = isset($filters['date_from']) ? $filters['date_from'] . ' 00:00:00' : null;
                $endDate = isset($filters['date_to']) ? $filters['date_to'] . ' 23:59:59' : null;

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('created_at', '<=', $endDate);
                }
            })->get();
    }


    public function findByTaskId($taskId): ?Article
    {
        return Article::with('objectType')->where('task_id', $taskId)->first();
    }

    public function findById($id): ?Article
    {
        return Article::with('objectType')->where('id', $id)->first();
    }

    public function findByReestr($filters)
    {
        return collect(['gnk_id', 'reestr_number', 'number_protocol'])
            ->some(fn($key) => isset($filters[$key]))
            ? Article::query()
                ->when(isset($filters['gnk_id']), fn($query) => $query->where('gnk_id', $filters['gnk_id']))
                ->when(isset($filters['reestr_number']), fn($query) => $query->where('reestr_number', $filters['reestr_number']))
                ->when(isset($filters['number_protocol']), fn($query) => $query->where('number_protocol', $filters['number_protocol']))
                ->get()
            : null;

    }

    public function findByCadastralNumber($number)
    {
        return Article::query()->where('cadastral_number', $number)->get();
    }

    public function setObjectUsers($user, $roleId, $id)
    {
        if (in_array($roleId, [UserRoleEnum::ICHKI->value, UserRoleEnum::TEXNIK->value, UserRoleEnum::MUALLIF->value])) {
            $existingHistory = ObjectUserHistory::query()
                ->where('user_id', $user->id)
                ->where('role_id', $roleId)
                ->where('object_id', $id)
                ->whereDate('check_at', Carbon::today())
                ->exists();

            if (!$existingHistory) {
                return ObjectUserHistory::query()->create([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'object_id' => $id,
                    'check_at' => Carbon::now()
                ]);
            }
        }

        return null;

    }

    public function getArticlesByUserRole($user, $roleId)
    {
        return $user->objects()
            ->wherePivot('role_id', $roleId);
    }

    public function getArticlesByRegion($regionId)
    {
        return Article::query()
            ->where('region_id', $regionId);
    }

    public function searchObjects($query, $filters)
    {
        return $query
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('articles.object_status_id', $filters['status']);
            })
            ->when(isset($filters['inspector_id']), function ($query) use ($filters) {
                if ($filters['inspector_id'] == 'not_inspector')
                    $query->whereDoesntHave('users', function ($query) use ($filters) {
                        $query
                            ->where('role_id', UserRoleEnum::INSPECTOR->value);
                    });
                else
                    $query->whereHas('users', function ($query) use ($filters) {
                        $query->where('user_id', $filters['inspector_id'])
                            ->where('role_id', UserRoleEnum::INSPECTOR->value);
                    });
            })
//            ->when(isset($filters['name']), function ($query) use ($filters) {
//                $query->searchByName($filters['name']);
//            })
            ->when(isset($filters['customer_name']), function ($query) use ($filters) {
                $query->searchByCustomerName($filters['customer_name']);
            })
            ->when(isset($filters['funding_source']), function ($query) use ($filters) {
                $query->where('funding_source_id', $filters['funding_source']);
            })
            ->when(isset($filters['sphere_id']), function ($query) use ($filters) {
                $query->where('sphere_id', $filters['sphere_id']);
            })
            ->when(isset($filters['null_sphere']), function ($query) use ($filters) {
                $query->whereNull('sphere_id');
                $query->whereNull('gnk_id');
            })
            ->when(isset($filters['object_type']), function ($query) use ($filters) {
                $query->where('object_type_id', $filters['object_type']);
            })
            ->when(isset($filters['task_id']), function ($query) use ($filters) {
                $query->searchByTaskId($filters['task_id']);
            })
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                $query->where('articles.region_id', $filters['region_id']);
            })
            ->when(isset($filters['inn']), function ($query) use ($filters) {
                $query->searchByInn($filters['inn']);
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
            ->when(isset($filters['district_id']), function ($query) use ($filters) {
                $query->where('articles.district_id', $filters['district_id']);
            })
            ->when(isset($filters['user_search']), function ($query) use ($filters) {
                $query->whereHas('users', function ($query) use ($filters) {
                    $query->searchByFullName($filters['user_search']);
                });
            });
    }

    public function rotateUsers($user, $roleId, $firstUserId, $secondUserId): void
    {
        $fistUser = User::query()->find($firstUserId);
        $secondUser = User::query()->find($secondUserId);

//        $firstUserArticles = ArticleUser::where('user_id', $firstUserId)
//            ->where('role_id', UserRoleEnum::INSPECTOR->value)
//            ->pluck('article_id');
//        $secondUserArticles = ArticleUser::where('user_id', $secondUserId)
//            ->where('role_id', UserRoleEnum::INSPECTOR->value)
//            ->pluck('article_id');

        $secondUserArticles = $secondUser->objects()
            ->wherePivot('role_id', UserRoleEnum::INSPECTOR->value)
            ->whereIn('articles.object_status_id', [ObjectStatusEnum::PROGRESS, ObjectStatusEnum::FROZEN, ObjectStatusEnum::SUSPENDED])
            ->pluck('articles.id');

        $firstUserArticles = $fistUser->objects()
            ->wherePivot('role_id', UserRoleEnum::INSPECTOR->value)
            ->whereIn('articles.object_status_id', [ObjectStatusEnum::PROGRESS, ObjectStatusEnum::FROZEN, ObjectStatusEnum::SUSPENDED])
            ->pluck('articles.id');


        DxaResponse::query()
            ->whereIn('dxa_response_status_id', [DxaResponseStatusEnum::IN_REGISTER, DxaResponseStatusEnum::SEND_INSPECTOR])
            ->where('inspector_id', $firstUserId)
            ->update(['inspector_id' => $secondUserId]);

        DxaResponse::query()
            ->whereIn('dxa_response_status_id', [DxaResponseStatusEnum::IN_REGISTER, DxaResponseStatusEnum::SEND_INSPECTOR])
            ->where('inspector_id', $secondUserId)
            ->update(['inspector_id' => $firstUserId]);

        ArticleUser::whereIn('article_id', $firstUserArticles)
            ->where('role_id', UserRoleEnum::INSPECTOR->value)
            ->update(['user_id' => $secondUserId]);

        ArticleUser::whereIn('article_id', $secondUserArticles)
            ->where('role_id', UserRoleEnum::INSPECTOR->value)
            ->update(['user_id' => $firstUserId]);

        Regulation::query()
            ->whereIn('object_id', $firstUserArticles)
            ->where('created_by_user_id', $firstUserId)
            ->where('created_by_role_id', UserRoleEnum::INSPECTOR->value)
            ->update([
                'created_by_user_id' => $secondUserId,
                'created_by_role_id' => UserRoleEnum::INSPECTOR->value,
            ]);

        Regulation::query()
            ->whereIn('object_id', $secondUserArticles)
            ->where('created_by_user_id', $secondUserId)
            ->where('created_by_role_id', UserRoleEnum::INSPECTOR->value)
            ->update([
                'created_by_user_id' => $firstUserId,
                'created_by_role_id' => UserRoleEnum::INSPECTOR->value,
            ]);
        $this->sendNotification($user, $firstUserId, $secondUserId, $roleId);
        $this->sendNotification($user, $secondUserId, $firstUserId, $roleId);
    }

    public function findArticleByParams($params)
    {
        return Article::query()
            ->when($params['task_id'], function ($query) use ($params) {
                $query->where('task_id', $params['task_id'])
                        ->orWhere('manual_task_id', $params['task_id']);
            })
            ->when($params['gnk_id'], function ($query) use ($params) {
                $query->where('gnk_id', $params['gnk_id']);
            })
            ->when($params['expertize_number'], function ($query) use ($params) {
                $query->where('number_protocol', $params['expertize_number']);
            })
            ->firstOrFail();
    }

    public function getUserByInnAndRole($inn, $role)
    {
        return User::query()
            ->where('identification_number', $inn)
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })
            ->first();
    }

    public function getAccountObjectsQuery($query, $status)
    {
        if ($status == 1) {
            $query->whereDoesntHave('paymentLogs')
                ->orWhereHas('paymentLogs', function ($q) {
                    $q->whereRaw("
                    (SELECT SUM(CAST(content->'additionalInfo'->>'amount' AS DECIMAL))
                     FROM article_payment_logs
                     WHERE article_payment_logs.gu_id = articles.id
                    ) = 0
                ");
                });
        } elseif ($status == 2) {
            $query->whereHas('paymentLogs', function ($q) {
                $q->whereRaw("
                (SELECT SUM(CAST(content->'additionalInfo'->>'amount' AS DECIMAL))
                 FROM article_payment_logs
                 WHERE article_payment_logs.gu_id = articles.id
                ) < CAST(articles.price_supervision_service AS DECIMAL)
            ");
            });
        } elseif ($status == 3) {
            $query->whereHas('paymentLogs', function ($q) {
                $q->whereRaw("
                (SELECT SUM(CAST(content->'additionalInfo'->>'amount' AS DECIMAL))
                 FROM article_payment_logs
                 WHERE article_payment_logs.gu_id = articles.id
                ) >= CAST(articles.price_supervision_service AS DECIMAL)
            ");
            });
        }

        return $query;

    }


    private function sendNotification($user, $firstUserId, $secondUserId, $roleId)
    {
        try {
            $firstUser = User::query()->find($firstUserId);
            $secondUser = User::query()->find($secondUserId);
            $role = Role::query()->find($roleId);
            $message = MessageTemplate::ratationInspector($firstUser->full_name, $secondUser->full_name, $user->full_name, $role->name, now());
            $firstUser->notify(new InspectorNotification(title: "Rotatsiya", message: $message, url: null, additionalInfo: null));

        } catch (\Exception $exception) {

        }
    }


    public function getTotalPaymentStatistics($regionId)
    {
        $articles = Article::with('paymentLogs')
            ->where('region_id', $regionId)
            ->get();

        return $articles;
    }
}
