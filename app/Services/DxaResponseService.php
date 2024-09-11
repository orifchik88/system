<?php

namespace App\Services;

use App\Enums\DxaResponseStatusEnum;
use App\Models\Block;
use App\Models\DxaResponse;
use App\Models\Rekvizit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $response->save();
        return $response;
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

    public function sendReject(): DxaResponse
    {
        $response = $this->findResponse();
        $response->dxa_response_status_id = DxaResponseStatusEnum::REJECTED;
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
            $path = $image->store('images/response', 'public');
            $model->images()->create(['url' => $path]);
        }
    }
}
