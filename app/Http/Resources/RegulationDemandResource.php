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
            'comment' => $this->comment,
            'act_status' => ActStatusResource::make($this->actStatus),
            'act_violation_type' => $this->act_violation_type_id,
            'title' => $this->actViolation->violation->title,
            'description' => $this->actViolation->violation->description,
            'images' => ImageResource::collection($this->whenLoaded('imagesFiles')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
