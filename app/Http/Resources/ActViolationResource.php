<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActViolationResource extends JsonResource
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
            'type' => $this->act_violation_type_id,
            'images' => ImageResource::collection($this->whenLoaded('imagesFiles')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
