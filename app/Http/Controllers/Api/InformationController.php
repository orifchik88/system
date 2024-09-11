<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\InformationService;
use GuzzleHttp\Client;
use Hamcrest\Arrays\SeriesMatchingOnce;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class InformationController extends BaseController
{

    public function __construct(public InformationService $informationService){}

    public function monitoringObjects(): JsonResponse
    {
        $customerInn = request('customer_inn');
        $pudratInn = request('pudrat_inn');
        try {
            $data = getData(config('app.gasn.monitoring'), request('expertise_number'))['data']['result']['data'];
            if (!empty($data))
            {
                if ($data[0]['end_term_work_days']){
                    $meta[] = $this->informationService->customer($customerInn, $pudratInn);
                }
                $meta[] = [
                    'id' => $data[0]['id'],
                    'gnk_id' => $data[0]['gnk_id'],
                    'project_type_id' => $data[0]['project_type_id'],
                    'name' => $data[0]['name'],
                    'end_term_work_days' => $data[0]['end_term_work_days'],
                ];
            }else{
                $meta[] = $this->informationService->customer($customerInn, $pudratInn);
            }


            return $this->sendSuccess($meta, 'Monitoring objects successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }

    public function monitoringCustomer(): JsonResponse
    {
        try {
            $customerInn = request('customer_inn');
            $pudratInn = request('pudrat_inn');
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $url = 'https://api.shaffofqurilish.uz/api/v1/request/monitoring-objects?customer_inn='.$customerInn.'&pudrat_inn='.$pudratInn;


            $resClient = $client->post($url,
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);
            $response = json_decode($resClient->getBody(), true);

//            return response()->json($response['result']['data']['result']['data'], 200);
//
//            if (isset($response['result']['data']['result']['data'])) {
//                foreach ($response['result']['data']['result']['data'] as &$item) {
//                    if (isset($item['pudrat_tender']) && is_array($item['pudrat_tender'])) {
//                        $item['pudrat_tender'] = array_values($item['pudrat_tender']);
//
//                        $item['pudrat_tender'] = array_filter($item['pudrat_tender'], function ($tender) use ($pudratInn) {
//                            return $tender['winner_inn'] == $pudratInn;
//                        });
//
//                        $item['pudrat_tender'] = array_values($item['pudrat_tender']);
//
//                        if (empty($item['pudrat_tender'])) {
//                            unset($item);
//                        }
//                    }
//                }
//            }

            if (isset($response['result']['data']['result']['data'])) {
                foreach ($response['result']['data']['result']['data'] as &$item) {
                    if (isset($item['pudrat_tender']) && is_array($item['pudrat_tender'])) {
                        $item['pudrat_tender'] = array_values($item['pudrat_tender']);

                        $item['pudrat_tender'] = array_filter($item['pudrat_tender'], function ($tender) use ($pudratInn) {
                            return $tender['winner_inn'] == $pudratInn;
                        });

                        if (!empty($item['pudrat_tender'])) {
                            usort($item['pudrat_tender'], function ($a, $b) {
                                return strtotime($b['confirmed_date']) - strtotime($a['confirmed_date']);
                            });

                            $item['pudrat_tender'] = [reset($item['pudrat_tender'])];
                        } else {
                            $item['pudrat_tender'] = [];
                        }

                        if (empty($item['pudrat_tender'])) {
                            unset($item);
                        }
                    }
                }

                $response['result']['data']['result']['data'] = array_values($response['result']['data']['result']['data']);
            }

            $data = $response['result']['data']['result']['data'][0];

            $meta = [
                'id' => $data['id'],
                'gnk_id' => $data['gnk_id'],
                'project_type_id' => $data['project_type_id'],
                'name' => $data['name'],
                'end_term_work_days' => $data['end_term_work_days']  ?? $data['pudrat_tender'][0]['end_term_work_days'],
            ];


            return $this->sendSuccess($meta, 'Monitoring customer information successfully.');
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
