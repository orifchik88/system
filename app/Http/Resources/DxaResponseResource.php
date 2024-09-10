<?php

namespace App\Http\Resources;

use App\Models\District;
use App\Models\Region;
use App\Models\User;
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
        $address = '';
        $monitoring = [];
        $region = Region::query()->find($this->region_id)->first();
        $district = District::query()->find($this->district_id)->first();
        if ($this->user_type == 'Yuridik shaxs')
        {
            $address = $this->address;
        }

        if ($this->user_type == 'Jismoniy shaxs')
        {
            $address = $this->permit_address;
        }

        if ($this->gnk_id){
            $data = getData(config('app.gasn.get_monitoring'), $this->gnk_id);

            $monitoring = $data['data']['result']['data'];

        }

        $inspector = User::query()->where('id', $this->inspector_id)->first();


        return [
            'id' =>$this->id,
            'user_type' => $this->user_type,
            'task_ids' => [],
            'task_id' =>$this->task_id,
            'status' => DxaResponseStatusResource::make($this->status),
            'deadline' => $this->deadline,
            'funding_source' => FundingSourceResource::make($this->fundingSource) ?? [],
            'monitoring_object' => !empty($monitoring) ? $monitoring : null,
            'price_supervision_service' => $this->price_supervision_service,
            'end_term_work' => $this->end_term_work,
            'sphere' => SphereResource::make($this->sphere),
            'program' => $this->program_id,
            'organization_name' => $this->organization_name,
            'stir' => $this->application_stir_pinfl,
            'pinfl' => $this->pinfl,
            "full_name" => $this->full_name,
            'legal_opf' => $this->legal_opf,
            'address' => $address,
            'administrative_status' => AdministrativeStatusResource::make($this->administrativeStatus),
            'passport' => $this->passport,
            'phone' => $this->phone,
            'email' => $this->email,
            'object_name' => $this->object_name,
            'cadastre_number' => $this->cadastral_number,
            'reestr_number' => $this->reestr_number,
            'tip_object' => $this->tip_object,
            'vid_object' => $this->vid_object,
            'is_accepted' => $this->is_accepted,
            'location_building' => $this->location_building,
            'region' => RegionResource::make($this->region),
            'district' => DistrictResource::make($this->district),
            'inspector_sent_at' => $this->inspector_sent_at,
            'inspector_answered_at' => $this->inspector_answered_at,
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
            'contractor_license_number' => $this->contractor_license_number,
            'contractor_license_date' => $this->contractor_license_date,
            'industrial_security_number' => $this->industrial_security_number,
            'industrial_security_date' => $this->industrial_security_date,
            'confirming_laboratory' => $this->confirming_laboratory,
            'specialists_certificates' => $this->specialists_certificates,
            'contract_file' => $this->contract_file,
            'organization_projects' => $this->organization_projects,
            'file_energy_efficiency' => $this->file_energy_efficiency,
            'inspector' => [
                'id' => $inspector ? $inspector->id : null,
                'name' =>  $inspector ? "{$inspector->surname} {$inspector->name} {$inspector->middle_name}" : null,
            ],
            'images' => ImageResource::collection($this->images),
            'inspector_comment' => $this->inspector_commit ?? null,
            'created_at' => $this->created_at,
            'supervisors' => DxaResponseSupervisorResource::collection($this->supervisors)
        ];
    }
}
