<?php

namespace App\Services;

use App\Enums\DxaResponseStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Block;
use App\Models\DxaResponse;
use App\Models\MonitoringObject;
use App\Models\Rekvizit;
use App\Models\Role;
use App\Models\User;
use App\Notifications\InspectorNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DxaResponseService
{
    public array $data = [];

    public function __construct(
        protected DxaResponse $dxaResponse
    )
    {
    }

    public function getRegisters($user, $roleId, $type)
    {
        switch ($roleId) {
            case UserRoleEnum::INSPECTOR->value:
                return $this->dxaResponse
                    ->where('inspector_id', $user->id)
                    ->where('notification_type', $type)
                    ->whereIn('dxa_response_status_id', [DxaResponseStatusEnum::NEW, DxaResponseStatusEnum::SEND_INSPECTOR, DxaResponseStatusEnum::IN_REGISTER]);
            case UserRoleEnum::INSPEKSIYA->value:
            case UserRoleEnum::HUDUDIY_KUZATUVCHI->value:
            case UserRoleEnum::REGISTRATOR->value:
                return $this->dxaResponse
                    ->where('region_id', $user->region_id)
                    ->where('notification_type', $type)
                    ->whereIn('dxa_response_status_id', [DxaResponseStatusEnum::NEW, DxaResponseStatusEnum::SEND_INSPECTOR, DxaResponseStatusEnum::IN_REGISTER]);
            case UserRoleEnum::RESPUBLIKA_KUZATUVCHI->value:
                return $this->dxaResponse
                    ->where('notification_type', $type)
                    ->whereIn('dxa_response_status_id', [DxaResponseStatusEnum::NEW, DxaResponseStatusEnum::SEND_INSPECTOR, DxaResponseStatusEnum::IN_REGISTER]);
            case UserRoleEnum::YURIST->value:
                return $this->dxaResponse->where('region_id', $user->region_id)
                    ->where('administrative_status_id', 6)
                    ->where('dxa_response_status_id', DxaResponseStatusEnum::REJECTED)
                    ->where('notification_type', $type);
            default:
                return $this->dxaResponse->whereRaw('1 = 0');
        }
    }

    public function sendInspector(): DxaResponse
    {
        DB::beginTransaction();
        try {
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
            if ($response->save()){
                $this->sendNotification($this->data['inspector_id'], $response->task_id);
            }
            DB::commit();
            return $response;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }


    }


    private function sendNotification($inspectorId, $taskId)
    {
        try {
            $inspector = User::query()->find($inspectorId)->first();
            $user = Auth::user();
            $data = [
                'screen' => 'register'
            ];
            $message = MessageTemplate::attachObjectInspector($user->full_name, $taskId, 'Registrator', now());
            $inspector->notify(new InspectorNotification(title: "Ro'yxatdan o'tkazish uchun ariza keldi", message: $message, url: null, additionalInfo: $data));

        } catch (\Exception $exception) {

        }

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
        DB::beginTransaction();
        try {
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
            $this->saveBlocks();
            $this->saveRekvizit();
            DB::commit();
            return $response;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
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
        $response = $this->findResponse();


        foreach ($this->data['blocks'] as $blockData) {

            $blockAttributes = [
                'name' => $blockData['name'],
                'floor' => $blockData['floor'] ?? null,
                'construction_area' => $blockData['construction_area'],
                'count_apartments' => $blockData['count_apartments'],
                'height' => $blockData['height'] ?? null,
                'length' => $blockData['length'] ?? null,
                'block_mode_id' => $blockData['block_mode_id'] ?? null,
                'block_type_id' => $blockData['block_type_id'] ?? null,
                'appearance_type' => $blockData['appearance_type'] ?? null,
                'created_by' => Auth::id(),
                'status' => true,
            ];
            $blockAttributes['block_number'] = $this->determineBlockNumber($blockData, $response);
            $articleBlock = Block::query()->where('block_number', $blockAttributes['block_number'])->first();

            if ($articleBlock) {
                $articleBlock->update($blockAttributes);
                $block = $articleBlock;
            } else {
                $block = Block::create($blockAttributes);
            }

            $response->blocks()->attach($block->id);
        }
    }


    private function determineBlockNumber($blockData, $response)
    {
        $lastBlock = Block::query()->orderBy('block_number', 'desc')->first();

        if ($response->notification_type == 1) {
            $lastNumber = $lastBlock ? $lastBlock->block_number : 999999;
            $blockNumber = $lastNumber + 1;
        } else {
            $blockNumber = $blockData['block_number'] ?? ($lastBlock ? $lastBlock->block_number + 1 : 1);
        }
        return $blockNumber;
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

        if ($model->notification_type == 2) {
            $response = DxaResponse::getResponse($model->old_task_id);
            $images = $response->images;
            foreach ($images as $image) {
                $newImage = $image->replicate();
                $newImage->imageable_id = $model->id;
                $newImage->imageable_type = get_class($model);
                $newImage->save();
            }
        }
        if (isset($this->data['images'])) {
            foreach ($this->data['images'] as $image) {
                $path = $image->store('images/response', 'public');
                $model->images()->create(['url' => $path]);
            }
        }
    }
}
