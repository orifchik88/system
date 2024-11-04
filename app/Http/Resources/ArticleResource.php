<?php

namespace App\Http\Resources;

use App\Models\Article;
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

        $dxaResponse = $this->response;
        $oldTaskIds = $dxaResponse ? $dxaResponse->getOldTaskIds($this->task_id) : null;
        return [
            'id' => $this->id,
            'name' =>$this->name,
            'task_ids' => $oldTaskIds,
            'region' => RegionResource::make($this->region),
            'district' => DistrictResource::make($this->district),
            'task_id' => $this->task_id,
            'address' => $this->address,
            'linear_type' => $this->linear_type,
            'sphere' => SphereResource::make($this->sphere),
            'program' => ProgramResource::make($this->program) ?? null,
            'construction_works' => $this->construction_works,
            'object_type' => ObjectTypeResource::make($this->objectType),
            'location_building' => $this->location_building,
            'organization_name' => $this->organization_name ?? null,
            'cadastral_number' => $this->cadastral_number,
            'name_expertise' => $this->name_expertise,
            'status' => ObjectStatusResource::make($this->objectStatus),
            'difficulty_category' => DifficultyCategoryResource::make($this->difficulty),
            'total_price' => $this->construction_cost,
            'price_supervision_service' => $this->price_supervision_service,
            'paid' => $this->totalAmount(),
            'lat' => $this->lat,
            'long' => $this->long,
            'number_protocol' => $this->number_protocol,
            'date_protocol' => $this->date_protocol,
            'funding_source' => FundingSourceResource::make($this->fundingSource),
            'payment_deadline' => $this->payment_deadline,
            'closed_at' => $this->closed_at,
            'object_sector' => ObjectSectorResource::make($this->objectSector),
            'deadline' => $this->deadline,
            'reestr_number' => $this->reestr_number,
            'gnk_id' => $this->gnk_id,
            'created_at' => $this->created_at,
            'organization_projects' => $this->organization_projects,
            'specialists_certificates' => $this->specialists_certificates,
            'contract_file' => $this->contract_file,
            'confirming_laboratory' => $this->confirming_laboratory,
            'file_energy_efficiency' => $this->file_energy_efficiency,
            'blocks' => ResponseBlockResource::collection($this->blocks),
            'users' => ArticleUserResource::collection($this->users),
            'payment_logs' => ArticlePaymentLogResource::collection($this->paymentLogs),
            'rating' => json_decode($this->rating),
            'is_old' => isset($this->old_id) ? true : false,
            'statistics' => $this->countMonitorings()
        ];
    }

    private function countMonitorings(): array
    {
        $regulations = $this->regulations;

        return [
            'monitoring_count' => $this->monitorings()->count(),
            'regulation_count' => $regulations->count(),
            'violation_count' => $this->regulations->flatMap(function ($regulation) {
                return $regulation->violations;
            })->unique('id')->count(),
            'finished_regulation_count' => $this->regulations->flatMap(function ($regulation) {
                return $regulation->where('');
            })->unique('id')->count(),
        ];
    }
}
