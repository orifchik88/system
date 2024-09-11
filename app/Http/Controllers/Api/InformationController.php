<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use GuzzleHttp\Client;
use Hamcrest\Arrays\SeriesMatchingOnce;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class InformationController extends BaseController
{
    public function monitoringObjects(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.monitoring'), request('expertise_number'));

            return $this->sendSuccess($data['data']['result']['data'], 'Monitoring objects successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }

    public function monitoringCustomer(): JsonResponse
    {
        try {
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $url = 'https://api.shaffofqurilish.uz/api/v1/request/monitoring-objects?customer_inn='.request('customer_inn').'&pudrat_inn='.request('pudrat_inn');


            $resClient = $client->post($url,
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);
            $response = json_decode($resClient->getBody(), true);
            return response()->json($response, 200);
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function reestr(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.reestr'), \request('reestr_number'));
            return $this->sendSuccess($data['data'], 'Reestr');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
    public function monitoringGNK(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.get_monitoring'), request('gnk_id'));
            return $this->sendSuccess($data['data']['result']['data'], 'Object');
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

    public function expertiseFiles(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.expertise'), \request('reestr_number'));
            return $this->sendSuccess($data['data']['data'], 'Expertise Files');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function checkUser(): JsonResponse
    {
        try {
            $client = new Client();
            $url = 'https://sso.egov.uz/sso/oauth/Authorization.do?grant_type=one_authorization_code&client_id=ccnis_uz&client_secret=ZSSlVilzjEXH42GgxO878ost&code='.request('code').'&redirect_url=https://ccnis.devmc.uz/login/oneid';
            $resClient = $client->post($url);
            $response = json_decode($resClient->getBody(), true);



            $client = new Client();
            $url = 'https://sso.egov.uz/sso/oauth/Authorization.do?grant_type=one_access_token_identify&client_id=ccnis_uz&client_secret=ZSSlVilzjEXH42GgxO878ost&access_token='.$response['access_token'].'&scope='.$response['scope'];
            $resClient = $client->post($url);
            $data = json_decode($resClient->getBody(), true);


            $user = User::query()->where('pinfl', $data['pin'])->first();

            if (!$user) throw new ModelNotFoundException('Foydalanuvchi topilmadi');

            $combinedData = $data['pin'] . ':' . $response['access_token'];

            $encodedData = base64_encode($combinedData);

            $meta = [
                'roles'=>RoleResource::collection($user->roles),
                'access_token' => $encodedData,
                'full_name' => $user->full_name
            ];

            return $this->sendSuccess($meta, 'Success');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

}
