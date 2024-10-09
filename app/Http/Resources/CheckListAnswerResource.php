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
            'block_id' => $this->block_id,
            'object_id' => $this->object_id,
            'block_name' => $this->block->name,
            'question_name' => $this->question->name,
            'deadline' => $this->created_at
        ];
    }
}
