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
        //$regulations = $this->regulations()->with('regulationViolations')->get();
        $regulations = $this->regulations()
            ->with(['regulationViolations:regulation_id,violation_id'])
            ->get();


//        $violationCount = $regulations->flatMap(function ($regulation) {
//            return $regulation->regulationViolations->pluck('violation_id');
//        })->unique()->count();

        $violationCount = $regulations
            ->pluck('regulationViolations.*.violation_id')
            ->flatten()
            ->unique()
            ->count();

        $user = User::query()->select(['id', 'surname', 'name', 'middle_name', 'phone'])
            ->find($this->created_by);

        $role = Role::query()->select(['id', 'name'])
            ->find($this->created_by_role);


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
                'id' => $this->created_by_role,
                'name' => $role?->name,
            ],
            'user' => [
                'id' => $this->created_by,
                'name' => $user ? "{$user->surname} {$user->name} {$user->middle_name}" : null,
                'phone' => $user?->phone,
            ],

        ];
    }
}
