<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseBlockResource extends JsonResource
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
            'name' => $this->name,
            'mode' => BlockModeResource::make($this->mode),
            'type' => BlockTypeResource::make($this->type),
            'block_number' => $this->block_number,
            'floor' => $this->floor,
            'construction_area' => $this->construction_area,
            'count_apartments' => $this->count_apartments,
            'height' => $this->height,
            'length' => $this->length,
            'status' => $this->status,
            'accepted' => $this->accepted,
            'appearance_type' => AppearanceTypeResource::make($this->appearanceType)
        ];
    }
}
