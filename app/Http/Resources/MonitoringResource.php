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
//        $violationCount = [];
//        foreach ($this->regulations as $regulation) {
//            $regViolation = RegulationViolation::query()->where('regulation_id', $regulation->id)->get();
//            foreach($regViolation as $violation) {
//                $violationCount[] = $violation->violation_id;
//            }
//        }
//        $uniqueViolationCount = array_unique($violationCount);

        $user = User::find($this->created_by);


        return [
            'id' => $this->id,
            'object_name' => $this?->object?->name,
            'task_id' => $this?->object?->task_id,
            'regulation_type' => RegulationTypeResource::make($this->regulationType),
            'regulation_count' => $this->regulations()->count(),
            'active_regulation_count' => $this->regulations()->where('regulation_status_id', '!=', 6)->count(),
            'violation_count' =>  $violationCount ?? 0,
            'checklists' => ChecklistResource::collection($this->checklists),
            'created_at' => $this->created_at,
            'role' => [
                'id' => $this->created_by_role,
                'name' => Role::query()->find($this->created_by_role)->name,
            ],
            'user' => [
                'id' => $this->created_by,
                'name' =>  $user ? "{$user->surname} {$user->name} {$user->middle_name}" : null,
                'phone' =>  $user ? $user->phone : null,
            ]

        ];
    }
}
