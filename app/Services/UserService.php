<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class UserService
{
//    private string $apiUrl = 'https://api.shaffofqurilish.uz/api/v1/get-egov-token';

    public function __construct(protected Client $client, protected User $user){}

    public function getAllUsers(): object
    {
        return  $this->user->query()
            ->when(request('search'), function ($query) {
                $query->searchByFullName(request('search'))
                    ->searchByPinfOrPhone(request('search'));
            })
            ->when(request('region_id'), function ($query) {
                $query->where('region_id', request('region_id'));
            })
            ->when(request('district_id'), function ($query) {
                $query->where('district_id', request('district_id'));
            })
            ->when(request('role_id'), function ($query) {
                $query->where('role_id', request('role_id'));
            })
            ->when(request('status'), function ($query) {
                $query->where('user_status_id', request('status'));
            })->paginate(\request('perPage', 10));
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
        } catch (BadResponseException $ex) {
            $response = $ex->getResponse()->getBody()->getContents();
        }

        return $response['result']['data']['data'][0];
    }
}
