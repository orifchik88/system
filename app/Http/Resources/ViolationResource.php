<?php

namespace App\Http\Resources;

use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'question_id' => $this->question_id,
            'level' => LevelResource::make($this->level),
            'check_list_status' => true,
            'blocks' => $this->blockViolations ? BlockResource::collection($this->blockViolations) : null,
            'images' => ImageResource::collection($this->imageFiles)
        ];
    }
}
