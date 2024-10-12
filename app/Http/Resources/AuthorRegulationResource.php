<?php

namespace App\Http\Resources;

use App\Models\WorkType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AuthorRegulationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authorImages = collect(json_decode($this->author_images, true))->map(function ($image) {
            return [
                'url' => Storage::disk('public')->url($image),
            ];
        });
        $images = collect(json_decode($this->images, true))->map(function ($image) {
            return [
                'url' => Storage::disk('public')->url($image),
            ];
        });
        return [
            'id' => $this->id,
            'object_name' => $this->object->name,
            'block_name' => $this->block->name,
            'author' => UserResource::make($this->author),
            'bases' => BasisResource::make($this->bases),
            'work_type' => WorkTypeResource::make($this->workType),
            'author_comment' => $this->author_comment,
            'comment' => $this->comment,
            'author_images' => $authorImages,
            'images' => $images ?? null,
        ];
    }
}
