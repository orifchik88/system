<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use App\Models\Block;
use App\Models\LawyerStatus;
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
            'task_id' => $this->object->task_id ?? null,
            'block_name' => $this->monitoring->block->name ?? null,
            'regulation_number' => $this->regulation_number,
            'deadline' => $this->deadline,
            'act_status' => ActStatusResource::make($this->actStatus),
            'regulation_status' => RegulationStatusResource::make($this->regulationStatus),
            'regulation_type' => RegulationTypeResource::make($this->regulationType),
            'lawyer_status' => LawyerStatusResource::make($this->lawyerStatus),
            'lawyer_status_type' => $this->lawyer_status_type,
            'violation_count' => $this->violations()->count(),
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
