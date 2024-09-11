<?php

namespace App\Services;

use App\Enums\DxaResponseStatusEnum;
use App\Models\Block;
use App\Models\DxaResponse;
use App\Models\MonitoringObject;
use App\Models\Rekvizit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DxaResponseService
{
    public array $data = [];

    public function __construct(
        protected DxaResponse  $dxaResponse
    ){}

    public function sendInspector(): DxaResponse
    {

        $response = $this->findResponse();
        $response->dxa_response_status_id = DxaResponseStatusEnum::SEND_INSPECTOR;
        $response->inspector_sent_at = Carbon::now();
        $response->inspector_id = $this->data['inspector_id'];
        $response->gnk_id = $this->data['gnk_id'];
        $response->funding_source_id = $this->data['funding_source_id'];
        $response->sphere_id = $this->data['sphere_id'];
        $response->program_id = $this->data['program_id'];
        $response->end_term_work = $this->data['end_term_work'];
        if ($this->data['funding_source_id'] == 2){
            $monitoring = $this->saveMonitoringObject($this->data['gnk_id']);
            $response->monitoring_object_id = $monitoring->id;
        }
        $response->save();
        return $response;
    }

    private function saveMonitoringObject($gnkId): MonitoringObject
    {
        $data = getData(config('app.gasn.get_monitoring'), $gnkId);
        $monitoring = $data['data']['result']['data'][0];

        $object = new MonitoringObject();
        $object->monitoring_object_id = $monitoring['id'];
        $object->project_type_id = $monitoring['project_type_id'];
        $object->name = $monitoring['name'];
        $object->gnk_id = $monitoring['gnk_id'];
        $object->end_term_work_days = $monitoring['end_term_work_days'];
        $object->save();

        return $object;
    }

    public function sendRegister(): DxaResponse
    {
        $response = $this->findResponse();
        $response->dxa_response_status_id = DxaResponseStatusEnum::IN_REGISTER;
        $response->administrative_status_id = $this->data['administrative_status_id'];
        $response->inspector_answered_at = Carbon::now();
        $response->long = $this->data['long'];
        $response->lat = $this->data['lat'];
        $response->inspector_commit = $this->data['commit'];
        $response->save();

        $this->saveImages();
        $this->saveBlocks();
        $this->saveRekvizit();
        return $response;
    }

    private function saveRekvizit()
    {
        $response = $this->findResponse();
        $rekvizit = Rekvizit::query()->where('region_id', $response->region_id)->first();
        $response->update([
            'rekvizit_id' => $rekvizit->id,
        ]);
    }

    private function saveBlocks(){
        foreach ($this->data['blocks'] as $block) {
             Block::create([
                 'dxa_response_id' => $block['dxa_response_id'],
                 'name' => $block['name'],
                 'floor' => $block['floor'],
                 'construction_area' => $block['construction_area'],
                 'count_apartments' => $block['count_apartments'],
                 'height' => $block['height'],
                 'length' => $block['length'],
                 'block_mode_id' => $block['block_mode_id'],
                 'block_type_id' => $block['block_type_id'],
                 'created_by' => Auth::id(),
                 'status' => true,
             ]);
        }
    }

    public  function sendReject($response, $comment): DxaResponse
    {
        $response->dxa_response_status_id = DxaResponseStatusEnum::REJECTED;
        $response->rejection_comment = $comment;
        $response->save();

        return $response;
    }

    public  function sendMyGovReject($response)
    {
        return Http::withBasicAuth(
            'qurilish.sohasida.nazorat.inspeksiya.201122919',
            'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_'
        )->post('https://my.gov.uz/notice-beginning-construction-works-v4/rest-api/update/id/' . $response->task_id . '/action/issue-amount', [
            "RejectNoticeV4FormNoticeBeginningConstructionWorks" => [
                "reject_reason" => $response->rejection_comment,
            ]
        ]);
    }

    private function findResponse(): ?DxaResponse
    {
        return $this->dxaResponse->where('task_id', $this->data['task_id'])->first() ?? null;
    }

    private function saveImages(): void
    {
        $model = $this->findResponse();

        foreach ($this->data['images'] as $image) {
            $path = $image->store('images/response', 'public');
            $model->images()->create(['url' => $path]);
        }
    }
}
