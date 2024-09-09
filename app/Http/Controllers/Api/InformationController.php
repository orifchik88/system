<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

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

    public function checkPinfl(): JsonResponse
    {
        try {
            $user = User::query()->where('pinfl', request('pinfl'))->first();
            if (!$user) throw new ModelNotFoundException('Foydalanuvchi topilmadi');
            return $this->sendSuccess(UserResource::make($user), 'user');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

}
