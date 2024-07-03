<?php

namespace App\Services;

use App\Enums\DxaResponseStatusEnum;
use App\Models\DxaResponse;
use Carbon\Carbon;

class DxaResponseService
{
    public array $data = [];

    public function __construct(
        protected DxaResponse  $dxaResponse
    ){}

    public function sendInspector(): DxaResponse
    {
        $response = $this->findResponse();
        $response->dxa_response_statuses_id = DxaResponseStatusEnum::SEND_INSPECTOR;
        $response->inspector_sent_at = Carbon::now();
        $response->inspector_id = $this->data['inspector_id'];
        $response->save();
        return $response;
    }

    public function sendRegister(): DxaResponse
    {
        $response = $this->findResponse();
        $response->dxa_response_statuses_id = DxaResponseStatusEnum::CHECKED;
        $response->inspector_answered_at = Carbon::now();
        $response->long = $this->data['long'];
        $response->lat = $this->data['lat'];
        $response->inspector_commit = $this->data['commit'];
        $response->save();

        $this->saveImages();
        return $response;
    }

    public function sendReject(): DxaResponse
    {
        $response = $this->findResponse();
        $response->dxa_response_statuses_id = DxaResponseStatusEnum::ARCHIVE;
        $response->rejection_comment = $this->data['reject_comment'];
        $response->save();
        return $response;
    }

    private function findResponse(): ?DxaResponse
    {
        return $this->dxaResponse->where('task_id', $this->data['task_id'])->first() ?? null;
    }

    private function saveImages(): void
    {
        $model = $this->findResponse();

        foreach ($this->data['images'] as $image) {
            $path = $image->store('images', 'public');
            $model->images()->create(['url' => $path]);
        }
    }
}
