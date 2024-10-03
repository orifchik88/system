<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationDemandResource extends JsonResource
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
            'act_status' => ActStatusResource::make($this->actStatus),
            'user' => UserResource::make($this->user),
            'role' => RoleResource::make($this->role),
            'act_violation_type' => $this->act_violation_type_id,
            'comment' => $this->comment,
            'images' => ImageResource::collection($this->images),
            'documents' => DocumentResource::collection($this->documents),
            'created_at' => $this->created_at,
        ];
    }
}
