<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationEventResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'role' => $this->role ? RoleResource::make($this->role) : null,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user?->name,
                'surname' => $this->user?->surname,
                'middle_name' => $this->user?->middle_name,
            ],
            'images' => $this->images ? ImageResource::collection($this->images) : null,
            'files' => $this->documents ? DocumentResource::collection($this->documents) : null,
        ];
    }
}
