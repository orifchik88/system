<?php

namespace App\Http\Resources;

use App\Models\RegulationViolation;
use App\Models\Role;
use App\Models\User;
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
        $regulations = $this->regulations()->with('regulationViolations')->get();

        $violationCount = $regulations->flatMap(function ($regulation) {
            return $regulation->regulationViolations->pluck('violation_id');
        })->unique()->count();

        return [
            'id' => $this->id,
            'object_name' => $this?->object?->name,
            'task_id' => $this?->object?->task_id,
            'regulation_type' => RegulationTypeResource::make($this->regulationType),
            'regulation_count' => $this->regulations()->count(),
            'active_regulation_count' => $this->regulations()->where('regulation_status_id', '!=', 6)->count(),
            'violation_count' =>  $violationCount ?? 0,
            'checklists' => $this->checklists->map(function ($checklist) {
                return [
                    'status' => $checklist->status,
                    'question_id' => $checklist->question ? $checklist->question->id : null,
                ];
            }),
            'created_at' => $this->created_at,
            'role' => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user ? "{$this->user->surname} {$this->user->name} {$this->user->middle_name}" : null,
                'phone' => $this->user->phone,
            ],

        ];
    }
}
