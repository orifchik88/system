<?php

namespace App\Http\Resources;

use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DxaResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $this->loadMissing([
            'status', 'fundingSource', 'monitoring', 'sphere', 'program', 'administrativeStatus',
            'documents', 'objectType', 'region', 'district', 'lawyerStatus', 'rekvizit',
            'supervisors', 'blocks', 'images'
        ]);
        $address = $this->user_type == 'Yuridik shaxs' ? $this->address : $this->permit_address;

        $inspector = User::find($this->inspector_id);


        return [
            'id' =>$this->id,
            'user_type' => $this->user_type,
            'task_ids' => $this->getOldTaskIds($this->task_id),
            'task_id' =>$this->task_id,
            'status' => new DxaResponseStatusResource($this->status),
            'deadline' => $this->deadline,
            'funding_source' => new FundingSourceResource($this->fundingSource),
            'monitoring_object' => new MonitoringObjectResource($this->monitoring),
            'price_supervision_service' => $this->price_supervision_service,
            'end_term_work' => $this->end_term_work,
            'sphere' => new SphereResource($this->sphere),
            'program' => new ProgramResource($this->program),
            'organization_name' => $this->organization_name,
            'stir' => $this->application_stir_pinfl,
            'pinfl' => $this->pinfl,
            'notification_type' => $this->notification_type,
            'lat' => $this->notification_type === 2
                ? ($this->lat ?? $this->getObject($this->old_task_id)->lat ?? null)
                : $this->lat,
            'long' => $this->notification_type === 2
                ? ($this->long ?? $this->getObject($this->old_task_id)->long ?? null)
                : $this->long,
            "full_name" => $this->full_name,
            'legal_opf' => $this->legal_opf,
            'linear_type' => $this->linear_type,
            'address' => $address,
            'administrative_status' => new AdministrativeStatusResource($this->administrativeStatus),
            'administrative_files' => DocumentResource::collection($this->documents),
            'passport' => $this->passport,
            'phone' => $this->phone,
            'email' => $this->email,
            'object_name' => $this->object_name,
            'cadastral_number' => $this->cadastral_number,
            'reestr_number' => $this->reestr_number,
            'tip_object' => $this->tip_object,
            'object_type' => new ObjectTypeResource($this->objectType),
            'vid_object' => $this->vid_object,
            'is_accepted' => $this->is_accepted,
            'location_building' => $this->location_building,
            'region' => new RegionResource($this->region),
            'district' => new DistrictResource($this->district),
            'inspector_sent_at' => $this->inspector_sent_at ?? null,
            'inspector_answered_at' => $this->inspector_answered_at,
            'category_object_dictionary' => $this->category_object_dictionary,
            'construction_works' => $this->construction_works,
            'number_protocol' => $this->number_protocol,
            'date_protocol' => $this->date_protocol,
            'cost' => $this->cost,
            'lawyer_status' => new LawyerStatusResource($this->lawyerStatus),
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
            'rejection_comment' => $this->rejection_comment,
            'contract_file' => $this->contract_file,
            'organization_projects' => $this->organization_projects,
            'file_energy_efficiency' => $this->file_energy_efficiency,
            'inspector' => [
                'id' => $inspector ? $inspector->id : null,
                'name' =>  $inspector ? "{$inspector->surname} {$inspector->name} {$inspector->middle_name}" : null,
            ],
            'images' => ImageResource::collection(
                $this->notification_type === 2 && $this->images->isEmpty()
                    ? $this->getResponse($this->old_task_id)->images ?? []
                    : $this->images
            ),
            'blocks' => ResponseBlockResource::collection(
                $this->notification_type === 2 && $this->blocks->isEmpty()
                    ? $this->getObject($this->old_task_id)->blocks ?? []
                    : $this->blocks
            ),
            'inspector_comment' => $this->notification_type === 2
                ? ($this->inspector_commit ?? $this->getResponse($this->old_task_id)->inspector_commit ?? null)
                : $this->inspector_commit,
            'created_at' => $this->created_at,
            'rejected_at' => $this->rejected_at,
            'confirmed_at' => $this->confirmed_at,
            'supervisors' => $this->supervisors ? DxaResponseSupervisorResource::collection($this->supervisors) : null,
            'rekvizit' => new RekvizitResource($this->rekvizit) ?? null,
        ];
    }
}
