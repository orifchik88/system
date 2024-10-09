<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckListAnswerResource extends JsonResource
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
            'object_name' => $this->object->name,
            'block_name' => $this->object->name,
            'images' => ImageResource::collection($this->images),
            'files' => DocumentResource::collection($this->documents)
        ];
    }
}
