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
            'count' => $this->regulations()->count(),
            'regulations' => RegulationResource::collection($this->regulations)
        ];
    }
}
