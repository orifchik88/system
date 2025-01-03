<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DxaResponseListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $this->loadMissing([
            'status', 'region', 'district', 'inspector'
        ]);


        return [
            'id' =>$this->id,
            'task_ids' => $this->getOldTaskIds($this->task_id),
            'task_id' =>$this->task_id,
            'region' => new RegionResource($this->region),
            'district' => new DistrictResource($this->district),
            'status' => new DxaResponseStatusResource($this->status),
            'deadline' => $this->deadline,
            'end_term_work' => $this->end_term_work,
            'organization_name' => $this->organization_name,
            "full_name" => $this->full_name,
            'legal_opf' => $this->legal_opf,
            'linear_type' => $this->linear_type,
            'object_name' => $this->object_name,
            'location_building' => $this->location_building,
            'created_at' => $this->created_at,
            'confirmed_at' => $this->confirmed_at,
            'inspector' => [
                'id' => $this->inspector ? $this->inspector->id : null,
                'name' =>  $this->inspector ? "{$this->inspector->surname} {$this->inspector->name} {$this->inspector->middle_name}" : null,
                'phone' =>  $this->inspector ? $this->inspector->phone : null,
            ],
        ];
    }
}
