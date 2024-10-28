<?php

namespace App\Http\Resources;

use App\Enums\ObjectStatusEnum;
use App\Models\User;
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
        $user = User::query()->where('pinfl', $this->stir_or_pinfl)->first();

        return [
            'id' => $this->id,
            'role' => RoleResource::make($this->position),
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
            'user_objects' => $user ? collect($user->roles)->map(function ($role) {
                return [
                    'role_name' => $role->name,
                    'object_count' => $this->objects()
                        ->whereIn('object_status_id', [
                            ObjectStatusEnum::PROGRESS,
                            ObjectStatusEnum::FROZEN,
                            ObjectStatusEnum::SUSPENDED
                        ])
                        ->where('role_id', $role->id)->count(),
                ];
            })->toArray() : null,
        ];
    }
}
