<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleListResource extends JsonResource
{

    public function toArray(Request $request): array
    {

//        $dxaResponse = $this->response;
//        $oldTaskIds = $dxaResponse ? $dxaResponse->getOldTaskIds($this->task_id) : null;
        return [
            'id' => $this->id,
            'name' =>$this->name,
//            'task_ids' => $oldTaskIds,
            'region' => RegionResource::make($this->region),
            'district' => DistrictResource::make($this->district),
            'task_id' => $this->task_id,
            'location_building' => $this->location_building,
            'organization_name' => $this->organization_name ?? null,
            'status' => ObjectStatusResource::make($this->objectStatus),
            'closed_at' => $this->closed_at,
            'deadline' => $this->deadline,
            'created_at' => $this->created_at,
            'paid' => $this->totalAmount(),
            'price_supervision_service' => $this->price_supervision_service,
        ];
    }

}
