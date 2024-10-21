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
            'task_id' => $this->object->task_id ?? null,
            'block' => ArticleBlockResource::make($this->block),
            'work_type_id' => $this->work_type_id,
            'object_name' => $this->object->name,
            'object_id' => $this->object_id,
            'question_name' => $this->question->name,
            'deadline' => $this->created_at
        ];
    }
}
