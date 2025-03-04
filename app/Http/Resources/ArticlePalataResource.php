<?php

namespace App\Http\Resources;

use App\Enums\ConstructionWork;
use App\Enums\UserRoleEnum;
use App\Models\ConstructionTypes;
use App\Models\DxaResponse;
use Carbon\Carbon;
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
        $customer = $this->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
        $builder = $this->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();
        $designer = $this->users()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
        $rating= json_decode($this->rating);
        $dxa = DxaResponse::query()->where('task_id', $this->task_id)->first();
        return [
            'doc_id' => $this->id,
            'app_date' => $this->created_at->format('Y-m-d'),
            'app_number' => $this->task_id,
            'obj_name' => $this->name,
            'region_id' => $this->region ? $this->region->soato : null,
            'region_name' => $this->region ? $this->region->name_uz: null,
            'district_id' => $this->district ? $this->district->soato : null,
            'district_name' => $this->district ? $this->district->name_uz : null,
            'customer_name' => $customer ? $customer->organization_name : null,
            'customer_tin' => $customer ? $customer->identification_number : null,
            'builder_name' => $builder ? $builder->organization_name : null,
            'builder_tin' => $builder ? $builder->identification_number : null,
            'rating_builder' => $rating ? ($rating[0]?->qurilish?->reyting_umumiy ?? null) : null,
            'designer_name' => $designer ? $designer->organization_name : null,
            'designer_tin' => $designer ? $designer->identification_number : null,
            'rating_designer' => $rating ? ($rating[0]?->loyiha?->reyting_umumiy ?? null) : null,
            'applicant_name' => $dxa ? $dxa->organization_name : null,
            'applicant_stir' =>$dxa ? $dxa->stir : null,
            'deadline' => $this->deadline,
            'finish_date' => $this->closed_at ? Carbon::parse($this->closed_at)->format('Y-m-d') : null,
            'obj_type' => $this->objectType ? $this->objectType->name : null,
            'complexity' => $this->difficulty ? $this->difficulty->difficulty : null,
            'build_type' => $this->construction_works,
            'build_cost' => $this->construction_cost,
            'industry' => $this->sphere ? $this->sphere->name_uz : null,
            'funding' => $this->fundingSource ? $this->fundingSource->description : null,
            ];
    }
}
