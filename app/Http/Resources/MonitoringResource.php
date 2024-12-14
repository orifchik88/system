<?php

namespace App\Http\Resources;

use App\Models\RegulationViolation;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $violationCount = [];
        foreach ($this->regulations as $regulation) {
            $regViolation = RegulationViolation::query()->where('regulation_id', $regulation->id)->get();
            foreach($regViolation as $violation) {
                $violationCount[] = $violation->violation_id;
            }
        }
        $uniqueViolationCount = array_unique($violationCount);


        return [
            'id' => $this->id,
            'object_name' => $this?->object?->name,
            'task_id' => $this?->object?->task_id,
            'regulation_type' => RegulationTypeResource::make($this->regulationType),
            'regulation_count' => $this->regulations()->count(),
            'active_regulation_count' => $this->regulations()->where('regulation_status_id', '!=', 6)->count(),
            'violation_count' =>  count($uniqueViolationCount) ?? 0,
            'checklists' => ChecklistResource::collection($this->checklists),
            'created_at' => $this->created_at,
        ];
    }
}
