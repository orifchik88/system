<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleLocationListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' =>$this->name,
            'task_id' => $this->task_id ?? $this->manual_task_id,
            'location_building' => $this->location_building,
            'organization_name' => $this->organization_name ?? null,
            'status' => ObjectStatusResource::make($this->objectStatus),
            'lat' => $this->lat,
            'long' => $this->long,
            'number_protocol' => $this->number_protocol,
            'reestr_number' => $this->reestr_number,
        ];
    }
}
