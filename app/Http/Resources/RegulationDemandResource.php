<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationDemandResource extends JsonResource
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
            'act_status' => ActStatusResource::make($this->actStatus),
            'act_violation_type' => $this->act_violation_type_id,
            'title' => $this->actViolation ? $this->actViolation->violation->title : null,
            'violation_id' => $this->actViolation ? $this->actViolation->violation->id : null,
            'blocks' => $this->actViolation ? ActBlockResource::collection($this->actViolation->blocks, $this->actViolation->id) : null
        ];
    }
}
