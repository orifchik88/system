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
            'block_name' => $this->regulation->monitoring->block->name,
        ];
    }
}
