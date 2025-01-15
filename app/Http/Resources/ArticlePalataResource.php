<?php

namespace App\Http\Resources;

use App\Enums\ConstructionWork;
use App\Models\ConstructionTypes;
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
            'region_id' => $this->region_id,
            'district_id' => $this->district_id,
            'address' => $this->address,
            'difficulty_category_id' => $this->difficulty_category_id,
            'construction_type_id' =>  $this->response ? ConstructionWork::fromString($this->response->construction_works)->value : 0,
            'construction_cost' => $this->cost,
            'object_images' => [],
            'blocks' => $this?->blocks()->pluck('id'),
            'object_status_id' => $this->object_status_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'object_type' => $this->object_type_id,
            'cadastral_number' => $this->cadastral_number,
            'name_expertise' => $this->name_expertise,
            'lat' => $this->lat,
            'long' => $this->long,
            'lat_long_status' => 0,
            'dxa_id' => $this->dxa_response_id,
            'task_id' => $this->task_id,
            'customer_id' => 0,
            'funding_source_id' => $this->funding_source_id,
            'closed_at' => $this->closed_at,
            'object_sector_id' => $this->object_sector_id,
            'object_category_id' => $this->object_category_id,
            'updated_by' => 0,
            'deadline' => $this->deadline,
            'gnk_id' => $this->gnk_id,
            ];
    }
}
