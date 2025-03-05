<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationFineResource extends JsonResource
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
            'user_type' => $this->user_type,
            'organization_name' => $this->organization_name,
            'inn' => $this->inn,
            'full_name' => $this->full_name,
            'pinfl' => $this->pinfl,
            'position' => $this->position,
            'decision_series' => $this->decision_series,
            'decision_number' => $this->decision_number,
            'substance' => $this->substance,
            'substance_item' => $this->substance_item,
            'amount' => $this->amount,
            'date' => $this->date,
            'files' => $this->documents ? DocumentResource::collection($this->documents) : null,
            'created_at' => $this->created_at,

        ];
    }
}
