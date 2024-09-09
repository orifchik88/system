<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DxaResponseSupervisorResource extends JsonResource
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
          'role' => RoleResource::make($this->role),
          'organization_name' => $this->organization_name,
          'identification_number' => $this->identification_number,
          'stir_or_pinfl' => $this->pinfl,
          'fish' => $this->fish,
          'passport_number' => $this->passport_number,
          'name_graduate_study' => $this->name_graduate_study,
          'specialization' => $this->specialization,
          'diplom_number' => $this->diplom_number,
          'diplom_date' => $this->diplom_date,
          'sertification_number' => $this->sertification_number,
          'phone_number' => $this->phone_number,
          'comment' => $this->comment,
        ];
    }
}
