<?php

namespace App\Http\Resources;

use App\Models\District;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DxaResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $region = Region::query()->where('soato', $this->region_id)->first();
        $district = District::query()->where('soato', $this->district_id)->first();
        return [
            'id' =>$this->id,
            'task_id' => $this->task_id,
            'organization_name' => $this->organization_name,
            'stir' => $this->application_stir_pinfl,
            'legal_opf' => $this->legal_opf,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'object_name' => $this->object_name,
            'cadastre_number' => $this->cadastral_number,
            'reestr_number' => $this->reestr_number,
            'tip_object' => $this->tip_object,
            'vid_object' => $this->vid_object,
            'location_building' => $this->location_building,
            'region' => $region->name_uz,
            'district' => $district->name_uz,
            'category_object_dictionary' => $this->category_object_dictionary,
            'construction_works' => $this->construction_works,
            'number_protocol' => $this->number_protocol,
            'date_protocol' => $this->date_protocol,
            'cost' => $this->cost,
            'object_parallel_design_number' => $this->object_parallel_design_number,
            'object_parallel_design_date' => $this->object_parallel_design_date,
            'object_state_program_number' => $this->object_state_program_number,
            'object_state_program_date' => $this->object_state_program_date,
            'name_expertise' => $this->name_expertise,
            'positive_opinion_number' => $this->positive_opinion_number,
            'contractor_licence_number' => $this->contractor_licence_number,
            'contractor_licence_date' => $this->contractor_licence_date,
            'industrial_security_number' => $this->industrial_security_number,
            'industrial_security_date' => $this->industrial_security_date,
            'confirming_laboratory' => $this->confirming_laboratory,
            'specialists_certificates' => $this->specialists_certificates,
            'contract_file' => $this->contract_file,
            'organization_projects' => $this->organization_projects,
            'file_energy_efficiency' => $this->file_energy_efficiency,
            'created_at' => $this->created_at,
            'supervisors' => DxaResponseSupervisorResource::collection($this->supervisors)
        ];
    }
}
