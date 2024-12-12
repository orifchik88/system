<?php

namespace App\Console\Commands;

use App\Enums\DxaResponseStatusEnum;
use App\Models\DxaResponse;
use App\Models\Rekvizit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ChangeStatusResponseCommand extends Command
{
    protected $signature = 'response:change-status';


    protected $description = 'Command description';


    public function handle()
    {
        DxaResponse::query()
            ->where('dxa_response_status_id', DxaResponseStatusEnum::NEW)
            ->where('region_id', 14)
            ->chunk(10, function ($responses) {
                foreach ($responses as $item) {
                    $response = $item->object_type_id == 2
                        ? $this->fetchTaskData($item->task_id)
                        : $this->fetchLinearTaskData($item->task_id);

                    $json = $response->json();

                    if (!empty($json['task']['status'])) {
                        $status = null;

                        switch ($json['task']['status']) {
                            case 'processed':
                                $status = DxaResponseStatusEnum::ACCEPTED;
                                break;
                            case 'rejected':
                                $status = DxaResponseStatusEnum::REJECTED;
                                break;
                        }

                        if ($status !== null) {
                            $item->update(['dxa_response_status_id' => $status]);
                        }else{
                            if ($item->notification_type == 2)
                            {
                                $item->update(['dxa_response_status_id' => DxaResponseStatusEnum::IN_REGISTER]);
                            }
                        }
                    }
                }
            });

//        DxaResponse::query()
//            ->where('dxa_response_status_id', DxaResponseStatusEnum::IN_REGISTER)
//            ->chunk(100, function ($responses) {
//                foreach ($responses as $response) {
//                   $this->saveRekvizit($response);
//                }
//            });
    }

    private function saveRekvizit($response)
    {
        $rekvizit = Rekvizit::query()->where('region_id', $response->region_id)->first();
        $response->update([
            'rekvizit_id' => $rekvizit->id,
            'price_supervision_service' => price_supervision((int)$response->cost)
        ]);
    }

    protected function fetchTaskData($taskId = null)
    {
        return Http::withBasicAuth(
            'qurilish.sohasida.nazorat.inspeksiya.201122919',
            'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_'
        )->get('https://my.gov.uz/notice-beginning-construction-works-v4/rest-api/get-task?id=' . $taskId);
    }

    protected function fetchLinearTaskData($taskId = null)
    {
        return Http::withBasicAuth(
            'qurilish.sohasida.nazorat.inspeksiya.201122919',
            'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_'
        )->get('https://my.gov.uz/registration-start-linear-object-v1/rest-api/get-task?id=' . $taskId);
    }
}
