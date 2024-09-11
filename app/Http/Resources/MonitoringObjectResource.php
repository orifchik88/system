<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringObjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->monitoring_object_id,
            'name' => $this->name,
            'gnk_id' => $this->gnk_id,
            'project_type_id' => $this->project_type_id,
            'end_term_work_days' => $this->end_term_work_days,
        ];
    }
}
