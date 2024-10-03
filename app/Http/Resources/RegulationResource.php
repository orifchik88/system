<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use App\Models\Block;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class RegulationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $fromUser = $this->createdByUser;
        $responsibleUser = $this->responsibleUser;
        return [
            'id' => $this->id,
            'object_name' => $this->object->name ?? null,
            'block_name' => $this->monitoring->block->name ?? null,
            'regulation_number' => $this->regulation_number,
            'deadline' => $this->deadline,
            'regulation_status' => RegulationStatusResource::make($this->regulationStatus),
            'regulation_type' => RegulationTypeResource::make($this->regulationType),
            'violations' => ViolationResource::collection($this->violations),
//            'demands' => RegulationDemandResource::collection($this->demands),
//            'act_violations' => ActViolationResource::collection($this->actViolations),
            'created_at' => $this->created_at,
            'deadline_asked' => $this->deadline_asked,
            'from_user' => [
                'role' => RoleResource::make($this->createdByRole) ?? null,
                'phone' => $fromUser->phone ?? null,
                'fish' => $fromUser ? "{$fromUser->surname} {$fromUser->name} {$fromUser->middle_name}" : null,
            ],
            'responsible_user' => [
                'role' => RoleResource::make($this->responsibleRole) ?? null,
                'phone' => $responsibleUser->phone ?? null,
                'fish' => $responsibleUser ? "{$responsibleUser->surname} {$responsibleUser->name} {$responsibleUser->middle_name}" : null,
            ],
        ];
    }
}
