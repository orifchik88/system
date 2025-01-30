<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\SphereResource;
use App\Models\DxaResponse;
use App\Models\Program;
use App\Models\Sphere;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class InformationService
{
    public function customer($customerInn, $pudratInn)
    {
        try {
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
            $meta = [];
            if (isset($response['result']['data']['result']['data'])) {
                foreach ($response['result']['data']['result']['data'] as $item) {
                    $sphere = Sphere::query()->find($item['object_types_id']);
                    $program = Program::query()->find($item['project_type_id']);
                    $meta[] = [
                        'id' => $item['id'],
                        'gnk_id' => $item['gnk_id'],
                        'project_type' => ProgramResource::make($program),
                        'sphere' => SphereResource::make($sphere),
                        'name' => $item['name'],
                        'end_term_work_days' => $item['end_term_work_days']  ?? $item['pudrat_tender'][0]['end_term_work_days'],
                    ];
                }
            }
            return $meta;
        } catch (\Exception $exception){
           throw new  $exception;
        }
    }

    public function getConclusionPDF($task_id)
    {
        $response  = DxaResponse::where('task_id', $task_id)->first();
        if (!$response) throw new NotFoundException('Ariza topilmadi');

        if ($response->object_type_id == 1){
            $url = config('app.mygov.linear');
        }else{
            $url = config('app.mygov.url');
        }
        $result = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($url . "/get-repo-list?id=$task_id")->object();
        if (isset($result->guid) && $result->guid) {
            $file = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($url . "/get-repo?guid=$result->guid")->object();

        } else {
            $result = (array)$result;
            if (!isset($result['status'])) {
                $count = count($result);
                if (isset($result[$count - 1]->guid) && $result[$count - 1]->guid)
                    $guid = $result[$count - 1]->guid;
                else
                    $guid = $result[0]->guid;
                $file = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get( $url. "/get-repo?guid=$guid");
            } else
                throw new NotFoundException('Xulosa topilmadi');
        }
        return $file->json();
    }
}
