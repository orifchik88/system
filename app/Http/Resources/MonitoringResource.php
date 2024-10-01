<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringResource extends JsonResource
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
            'regulation_type' => RegulationTypeResource::make($this->regulationType),
            'regulation_count' => $this->regulations()->count(),
//            'active_count' => $this->regulations()->where('status', 2)->count(),
//            'closed_count' => $this->regulations()->where('status', 6)->count(),
//            'closed_expired' => $this->regulations()->where('status', 6)->where('is_administrative', true)->count(),
            'comment' => $this->comment,
            'checklists' => ChecklistResource::collection($this->checklists),
            'is_monitoring' => $this->comment ? true : false,
            'images' => ImageResource::collection($this->images)
        ];
    }
}
