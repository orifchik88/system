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
            $data = getData(config('app.gasn.monitoring'));
            return $this->sendSuccess($data['data']['result']['data'], 'Monitoring objects successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }
    public function programs(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.programs'));
            return $this->sendSuccess($data['data']['data'], 'Dastur');

        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function reestr(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.reestr'), \request('reestr_number'));
            return $this->sendSuccess($data['data']['data'], 'Reestr');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function rating(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.rating'), \request('inn'));
            return $this->sendSuccess($data['data']['data'], 'Reyting');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function conference(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.conference'), \request('conc'));
            return $this->sendSuccess($data['data'], 'Kengash');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

}
