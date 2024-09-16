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
        protected DxaResponse $dxaResponse
    )
    {
    }

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
        if ($this->data['funding_source_id'] == 2) {
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
        $response->price_supervision_service = price_supervision((int)$response->cost);
        $response->long = $this->data['long'];
        $response->lat = $this->data['lat'];
        $response->inspector_commit = $this->data['commit'];
        $response->save();

        $this->saveImages();
//        $this->saveDocuments();
        $this->saveBlocks();
        $this->saveRekvizit();
        return $response;
    }

    private function saveDocuments()
    {
        $model = $this->findResponse();

        foreach ($this->data['documents'] as $document) {
            $path = $document->store('documents/administrative-files', 'public');
            $model->documents()->create(['url' => $path]);
        }
    }

    private function saveRekvizit()
    {
        $response = $this->findResponse();
        $rekvizit = Rekvizit::query()->where('region_id', $response->region_id)->first();
        $response->update([
            'rekvizit_id' => $rekvizit->id,
        ]);
    }

    private function saveBlocks()
    {

        foreach ($this->data['blocks'] as $blockData) {
            $response = $this->findResponse();

            $blockAttributes = [
                'name' => $blockData['name'],
                'floor' => $blockData['floor'],
                'construction_area' => $blockData['construction_area'],
                'count_apartments' => $blockData['count_apartments'],
                'height' => $blockData['height'],
                'length' => $blockData['length'],
                'block_mode_id' => $blockData['block_mode_id'],
                'block_type_id' => $blockData['block_type_id'],
                'created_by' => Auth::id(),
                'status' => true,
            ];
            $blockAttributes['block_number'] = $this->determineBlockNumber($blockData, $response);
            $block = Block::create($blockAttributes);

            $block->responses()->attach($blockData['dxa_response_id']);
        }
    }

    private function determineBlockNumber($blockData, $response)
    {
        $lastBlock = Block::query()->orderBy('block_number', 'desc')->first();
        if ($response->notification_type == 1) {
            $lastNumber = $lastBlock ? $lastBlock->block_number : 999999;
            $blockData['block_number'] = $lastNumber + 1;
        } else {
                $blockData['block_number'] ?? ($lastBlock ? $lastBlock->block_number + 1 : 1);
        }

        return $blockData['block_number'];
    }


    public function sendReject($response, $comment): DxaResponse
    {
        $response->dxa_response_status_id = DxaResponseStatusEnum::REJECTED;
        $response->rejection_comment = $comment;
        $response->rejected_at = now();
        $response->save();

        return $response;
    }

    public function sendMyGovReject($response)
    {
        $authUsername = config('app.mygov.login');
        $authPassword = config('app.mygov.password');

        if ($response->object_type_id == 2) {
            $apiUrl = config('app.mygov.url') . '/update/id/' . $response->task_id . '/action/reject-notice';
            $formName = 'RejectNoticeV4FormNoticeBeginningConstructionWorks';
        } else {
            $apiUrl = config('app.mygov.linear') . '/update/id/' . $response->task_id . '/action/reject-notice';
            $formName = 'RejectNoticeFormRegistrationStartLinearObject';
        }

        return Http::withBasicAuth($authUsername, $authPassword)
            ->post($apiUrl, [
                $formName => [
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
