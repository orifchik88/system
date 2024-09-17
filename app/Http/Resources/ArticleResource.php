<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'name' =>$this->name,
            'region' => RegionResource::make($this->region),
            'district' => DistrictResource::make($this->district),
            'task_id' => $this->task_id,
            'address' => $this->location_building,
            'organization_name' => $this->response->organization_name ?? null,
            'costumer' => [
                'address' => $this->address,
            ],
            'cadastral_number' => $this->cadastral_number,
            'name_expertise' => $this->name_expertise,
            'object_status' => ObjectStatusResource::make($this->objectStatus),
            'difficulty_category' => DifficultyCategoryResource::make($this->difficulty),
            'total_price' => $this->construction_cost,
            'lat' => $this->lat,
            'long' => $this->long,
            'number_protocol' => $this->number_protocol,
            'date_protocol' => $this->date_protocol,
            'funding_source' => FundingSourceResource::make($this->fundingSource),
            'payment_deadline' => $this->payment_deadline,
            'object_sector' => ObjectSectorResource::make($this->objectSector),
            'deadline' => $this->deadline,
            'reestr_number' => $this->reestr_number,
            'created_at' => $this->created_at,
            'files' => [
                'organization_projects' => $this->organization_projects,
                'specialists_certificates' => $this->specialists_certificates,
                'contract_file' => $this->contract_file,
                'confirming_laboratory' => $this->confirming_laboratory,
                'file_energy_efficiency' => $this->file_energy_efficiency,
            ],
            'blocks' => ArticleBlockResource::collection($this->articleBlocks),
            'users' => ArticleUserResource::collection($this->users),
        ];
    }
}
