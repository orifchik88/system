<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\Translation\t;

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
                return $user->employees();
            case UserRoleEnum::QURILISH_MONTAJ->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->where('id', UserRoleEnum::INSPECTOR->value);
                })->where('region_id', $user->region_id);
            case UserRoleEnum::RESKADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('id', '!=', [
                        UserRoleEnum::BUYURTMACHI->value,
                        UserRoleEnum::LOYIHA->value,
                        UserRoleEnum::QURILISH->value,
                        UserRoleEnum::ICHKI->value,
                        UserRoleEnum::TEXNIK->value,
                        UserRoleEnum::MUALLIF->value,
                    ]);
                });
            case UserRoleEnum::REGKADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('id', '!=', [
                        UserRoleEnum::BUYURTMACHI->value,
                        UserRoleEnum::LOYIHA->value,
                        UserRoleEnum::QURILISH->value,
                        UserRoleEnum::ICHKI->value,
                        UserRoleEnum::TEXNIK->value,
                        UserRoleEnum::MUALLIF->value,
                    ]);
                })->where('region_id', $user->region_id);
            case UserRoleEnum::SEOM_RES_KADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('id', [UserRoleEnum::SEOM_REG_KADR->value, UserRoleEnum::SEOM->value]);
                });

            case UserRoleEnum::SEOM_REG_KADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->where('id', UserRoleEnum::SEOM->value);
                });
            case UserRoleEnum::FVV_RES_KADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('id', [UserRoleEnum::FVB_REG_KADR->value, UserRoleEnum::FVB->value]);
                });
            case UserRoleEnum::FVB_REG_KADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->where('id', UserRoleEnum::FVB->value);
                });
            case UserRoleEnum::NOGIRONLAR_JAM_KADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->where('id', UserRoleEnum::NOGIRONLAR_JAM->value);
                });
            case UserRoleEnum::NOGIRONLAR_ASSOT_KADR->value:
                return User::query()->whereHas('roles', function ($query) use ($user) {
                    $query->where('id', UserRoleEnum::NOGIRONLAR_ASSOT->value);
                });
            default:
                return User::query()->whereRaw('1 = 0');

        }
//        $auth = Auth::user();
//        $users = $this->user->query()
//            ->when(request('search'), function ($query) {
//                $query->searchByFullName(request('search'))
//                    ->searchByPinfOrPhone(request('search'));
//            })
//            ->when(request('region_id'), function ($query) {
//                $query->where('region_id', request('region_id'));
//            })
//            ->when(request('district_id'), function ($query) {
//                $query->where('district_id', request('district_id'));
//            })
//            ->when(request('status'), function ($query) {
//                $query->where('user_status_id', request('status'));
//            })
//            ->when(request('role_id'), function ($query) {
//                $query->whereHas('roles', function ($query){
//                    $query->where('role_id', request('role_id'));
//                });
//            });
//
//        if ($auth->isKadr()){
//            return $users->paginate(\request('perPage', 10));
//        }else{
//            return $users->where('created_by', $auth->id)->paginate(\request('perPage', 10));
//        }
    }

    public function searchByUser($query, $filters)
    {
         return $query->when(isset($filters['search']), function ($q) use($filters){
             $q->searchByFullName($filters['search'])
                    ->searchByPinfOrPhone($filters['search']);
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
