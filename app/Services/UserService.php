<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class UserService
{

    public function __construct(protected Client $client, protected User $user)
    {
    }

    public function getAllUsers($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::BUYURTMACHI->value:
            case UserRoleEnum::LOYIHA->value:
            case UserRoleEnum::QURILISH->value:
                return $user->employees()->whereNot('user_status_id', UserStatusEnum::RELEASED);

            case UserRoleEnum::QURILISH_MONTAJ->value:
                return User::query()->whereNot('user_status_id', UserStatusEnum::RELEASED)->whereHas('roles', function ($query) {
                    $query->where('role_id', UserRoleEnum::INSPECTOR->value);
                })->where('region_id', $user->region_id);

            case UserRoleEnum::RESKADR->value:
                return User::query()->whereHas('roles', function ($query) {
                    $query->whereNotIn('role_id', [
                        UserRoleEnum::BUYURTMACHI->value,
                        UserRoleEnum::LOYIHA->value,
                        UserRoleEnum::QURILISH->value,
                        UserRoleEnum::ICHKI->value,
                        UserRoleEnum::TEXNIK->value,
                        UserRoleEnum::MUALLIF->value,
                    ]);
                });

            case UserRoleEnum::REGKADR->value:
                return User::query()->whereHas('roles', function ($query) {
                    $query->whereNotIn('role_id', [
                        UserRoleEnum::BUYURTMACHI->value,
                        UserRoleEnum::LOYIHA->value,
                        UserRoleEnum::QURILISH->value,
                        UserRoleEnum::ICHKI->value,
                        UserRoleEnum::TEXNIK->value,
                        UserRoleEnum::MUALLIF->value,
                    ]);
                })->where('region_id', $user->region_id);

            case UserRoleEnum::SEOM_RES_KADR->value:
                return User::query()->whereNot('user_status_id', UserStatusEnum::RELEASED)->whereHas('roles', function ($query) {
                    $query->whereIn('role_id', [UserRoleEnum::SEOM_REG_KADR->value, UserRoleEnum::SEOM->value]);
                });

            case UserRoleEnum::SEOM_REG_KADR->value:
                return User::query()
                    ->whereNot('user_status_id', UserStatusEnum::RELEASED)
                    ->where('region_id', $user->region_id)
                    ->whereHas('roles', function ($query) {
                        $query->where('role_id', UserRoleEnum::SEOM->value);
                    });

            case UserRoleEnum::FVV_RES_KADR->value:
                return User::query()->whereNot('user_status_id', UserStatusEnum::RELEASED)->whereHas('roles', function ($query) {
                    $query->whereIn('role_id', [UserRoleEnum::FVB_REG_KADR->value, UserRoleEnum::FVB->value]);
                });

            case UserRoleEnum::FVB_REG_KADR->value:
                return User::query()
                    ->whereNot('user_status_id', UserStatusEnum::RELEASED)
                    ->where('region_id', $user->region_id)
                    ->whereHas('roles', function ($query) {
                        $query->where('role_id', UserRoleEnum::FVB->value);
                    });

            case UserRoleEnum::NOGIRONLAR_JAM_KADR->value:
                return User::query()->whereNot('user_status_id', UserStatusEnum::RELEASED)->whereHas('roles', function ($query) {
                    $query->where('role_id', UserRoleEnum::NOGIRONLAR_JAM->value);
                });

            case UserRoleEnum::NOGIRONLAR_ASSOT_KADR->value:
                return User::query()->whereNot('user_status_id', UserStatusEnum::RELEASED)->whereHas('roles', function ($query) {
                    $query->where('role_id', UserRoleEnum::NOGIRONLAR_ASSOT->value);
                });

            default:
                return User::query()->whereRaw('1 = 0');
        }
    }

    public function searchByUser($query, $filters)
    {
         return $query->when(isset($filters['search']), function ($q) use($filters){
             $q->searchAll($filters['search']);
            })
            ->when(isset($filters['region_id']), function ($q) use($filters){
                $q->where('region_id', $filters['region_id']);
            })
            ->when(isset($filters['district_id']), function ($q) use($filters) {
                $q->where('district_id', $filters['district_id']);
            })
            ->when(isset($filters['status']), function ($q) use($filters){
                $q->where('user_status_id', $filters['status']);
            })
            ->when(isset($filters['role_id']), function ($q) use($filters) {
                $q->whereHas('roles', function ($q) use($filters){
                    $q->where('role_id', $filters['role_id']);
                });
            });
    }

    public function getCountByUsers($user, $roleId): array
    {
        $query = $this->getAllUsers($user, $roleId)->getQuery();
        return [
            'all' => (clone $query)->count(),
            'active' => (clone $query)->where('user_status_id', UserStatusEnum::ACTIVE)->count(),
            'on_holiday' => (clone $query)->where('user_status_id', UserStatusEnum::ON_HOLIDAY)->count(),
            'released' => (clone $query)->where('user_status_id', UserStatusEnum::RELEASED)->count(),
        ];
    }

    public function getInfo(string $pinfl, string $birth_date)
    {
        try {
            $resClient = $this->client->post(config('app.passport.url') . '?pinfl=' . $pinfl . '&birth_date=' . $birth_date,
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode(config('app.passport.login') . ':' . config('app.passport.password')),
                    ]
                ]);

            $response = json_decode($resClient->getBody(), true);

            return $response['result']['data']['data'][0];

        } catch (BadResponseException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
