<?php

namespace App\Repositories;

use App\Enums\UserRoleEnum;
use App\Models\Article;
use App\Models\ArticleUser;
use App\Models\User;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
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

    public function findByTaskId($taskId): ?Article
    {
        return Article::where('task_id', $taskId)->first();
    }

    public function getArticlesByUserRole($user, $roleId)
    {
        return $user->objects()->wherePivot('role_id', $roleId);
    }

    public function getArticlesByRegion($regionId)
    {
        return Article::query()->where('region_id', $regionId);
    }

    public function searchObjects($query, $filters)
    {
        return $query
            ->when($filters['status'], function ($query, $status) {
                $query->where('articles.object_status_id', $status);
            })
            ->when($filters['name'], function ($query, $name) {
                $query->searchByName($name);
            })
            ->when($filters['customer'], function ($query, $customer) {
                $query->searchByOrganization($customer);
            })
            ->when($filters['funding_source'], function ($query, $fundingSource) {
                $query->where('funding_source_id', $fundingSource);
            })
            ->when($filters['object_type'], function ($query, $objectType) {
                $query->where('object_type_id', $objectType);
            })
            ->when($filters['task_id'], function ($query, $taskId) {
                $query->searchByTaskId($taskId);
            })
            ->when($filters['region_id'], function ($query, $regionId) {
                $query->where('articles.region_id', $regionId);
            })
            ->when($filters['district_id'], function ($query, $districtId) {
                $query->where('articles.district_id', $districtId);
            })
            ->when($filters['user_search'], function ($query, $userSearch) {
                $query->whereHas('users', function ($query) use ($userSearch) {
                    $query->searchByFullName($userSearch);
                });
            });
    }

    public function rotateUsers($firstUserId, $secondUserId): void
    {
        $firstUserArticles = ArticleUser::where('user_id',$firstUserId)
            ->where('role_id', UserRoleEnum::INSPECTOR->value)
            ->pluck('article_id');

        $secondUserArticles = ArticleUser::where('user_id', $secondUserId)
            ->where('role_id', UserRoleEnum::INSPECTOR->value)
            ->pluck('article_id');

        ArticleUser::whereIn('article_id', $firstUserArticles)
            ->where('role_id', UserRoleEnum::INSPECTOR->value)
            ->update(['user_id' => $secondUserId]);

        ArticleUser::whereIn('article_id', $secondUserArticles)
            ->where('role_id', UserRoleEnum::INSPECTOR->value)
            ->update(['user_id' => $firstUserId]);
    }

    public function findArticleByParams($params)
    {
        return Article::query()
            ->when($params['task_id'], function ($query) use ($params) {
                $query->where('task_id', $params['task_id']);
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
                    $q->select(DB::raw('SUM(CAST(content->\'additionalInfo\'->>\'amount\' AS DECIMAL)) as total_paid'))
                        ->groupBy('gu_id')
                        ->havingRaw('SUM(CAST(content->\'additionalInfo\'->>\'amount\' AS DECIMAL)) = 0');
                });
        } elseif ($status == 2) {
            $query->whereHas('paymentLogs', function ($q) {
                $q->select(DB::raw('SUM(CAST(content->\'additionalInfo\'->>\'amount\' AS DECIMAL)) as total_paid'))
                    ->groupBy('gu_id')
                    ->havingRaw('SUM(CAST(content->\'additionalInfo\'->>\'amount\' AS DECIMAL)) < CAST(price_supervision_service AS DECIMAL)');
            });
        } elseif ($status == 3) {
            $query->whereHas('paymentLogs', function ($q) {
                $q->select(DB::raw('SUM(CAST(content->\'additionalInfo\'->>\'amount\' AS DECIMAL)) as total_paid'))
                    ->groupBy('gu_id')
                    ->havingRaw('SUM(CAST(content->\'additionalInfo\'->>\'amount\' AS DECIMAL)) >= CAST(price_supervision_service AS DECIMAL)');
            });
        }

        return $query;

    }


    public function getTotalPaymentStatistics($regionId)
    {
        $articles = Article::with('paymentLogs')
            ->where('region_id', $regionId)
            ->get();

        return $articles;
    }
}
