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
            'active_count' => $this->regulations()->where('status', 2)->count(),
            'cl_count' => $this->regulations()->where('status', 2)->count(),
            'comment' => $this->comment,
            'is_monitoring' => $this->comment ? true : false,
            'images' => ImageResource::collection($this->images)
        ];
    }
}
