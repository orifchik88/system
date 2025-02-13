<?php

namespace App\Http\Resources;

use App\Models\RegulationViolation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function PHPUnit\Framework\stringContains;

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
            'work_in_progress' => $this->work_in_progress,
            'question_64' => $this->question_64,
            'question_65' => $this->question_65,
            'question_73' => $this->question_73,
//            'checklists' => $this->checklists->map(function ($checklist) {
//                return [
//                    'status' => $checklist->status,
//                    'question_id' => $checklist->question ? $checklist->question->id : null,
//                ];
//            }),

            'checklists' => $this->constant_checklist ? collect(json_decode($this->constant_checklist, true))->map(function ($status, $question_id) {
                return [
                    'question_id' => (int)$question_id,
                    'status' => (int)$status,
                ];
            })->values() : [],
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
