<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationViolationResource extends JsonResource
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
            'title' => $this->violation->title,
            'comment' => $this->violation->description,
            'block_name' => $this->regulation?->monitoring?->block?->name,
            'normative_doc_name' => $this?->violation?->bases?->topic?->normative->name,
            'topic_name' => $this?->violation?->bases->topic->name,
            'bases_name' => $this?->violation?->bases->name,
            'images' => ImageResource::collection($this?->violation->images),
        ];
    }
}
