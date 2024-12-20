<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticlePalataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'region' => $this?->region->name_uz,
            'region_id' => $this->region_id,
            'district' => $this?->district->name_uz,
            'district_id' => $this->district_id,
            'address' => $this->address,
            'difficulty_category_name' => $this->difficulty->difficulty,
            'difficulty_category_id' => $this->difficulty_category_id,
            'construction_type' => $this?->response->construction_works,
            'construction_cost' => $this->cost,
            'blocks' => $this?->blocks()->pluck('id'),
            'object_status_id' => $this->object_status_id,
            'object_status' => $this?->objectStatus->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'object_type' => $this->object_type_id,
            'cadastral_number' => $this->cadastral_number,
            'name_expertise' => $this->name_expertise,
            'lat' => $this->lat,
            'long' => $this->long,
            'dxa_id' => $this->dxa_response_id,
            'task_id' => $this->task_id,
            'funding_source' => $this?->fundingSource->name,
            'funding_source_id' => $this->funding_source_id,
            'closed_at' => $this->closed_at,
            'object_sector' => $this->objectSector->name,
            'object_sector_id' => $this->object_sector_id,
            'deadline' => $this->deadline,
            'gnk_id' => $this->gnk_id,
            ];
    }
}
