<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InformationController extends BaseController
{
    public function monitoringObjects(): JsonResponse
    {
        try {
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $resClient = $client->post('https://api.shaffofqurilish.uz/api/v1/request/monitoring-objects',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);
            $response = json_decode($resClient->getBody(), true);
            return $this->sendSuccess($response['result']['data']['result']['data'], 'Monitoring objects successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }
    public function programs(): JsonResponse
    {
        try {
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $resClient = $client->post('https://api.shaffofqurilish.uz/api/v1/request/monitoring-dasturlar',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);
            $response = json_decode($resClient->getBody(), true);
            return $this->sendSuccess($response['result']['data']['data'], 'Monitoring objects successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function reestr(): JsonResponse
    {
        try {
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $resClient = $client->post('https://api.shaffofqurilish.uz/api/v1/request/opendata-reestr?reestr_number='.request('reestr_number'),
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);
            $response = json_decode($resClient->getBody(), true);
            return $this->sendSuccess($response['result']['data']['data'], 'Monitoring objects successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function reyting(): JsonResponse
    {
        try {
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $resClient = $client->post('https://api.shaffofqurilish.uz/api/v1/request/reyting?INN='.request('inn'),
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);
            $response = json_decode($resClient->getBody(), true);
            return $this->sendSuccess($response['result']['data']['data'], 'Monitoring objects successfully.');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

}
