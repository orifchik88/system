<?php

namespace App\Http\Resources;

use App\Models\WorkType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorRegulationResource extends JsonResource
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
            'block_name' => $this->block->name,
            'author' => UserResource::make($this->author),
            'bases' => BasisResource::make($this->bases),
            'work_type' => WorkTypeResource::make($this->workType),
            'author_comment' => $this->author_comment,
            'comment' => $this->comment,
            'author_images' => $this->author_images,
            'images' => $this->images,
        ];
    }
}
