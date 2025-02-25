<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleListResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $inspector = $this->users()->where('role_id', UserRoleEnum::INSPECTOR->value)->first();
        return [
            'id' => $this->id,
            'name' =>$this->name,
            'region' => RegionResource::make($this->region),
            'district' => DistrictResource::make($this->district),
            'task_id' => $this->task_id,
            'sphere' => SphereResource::make($this->sphere),
            'location_building' => $this->location_building,
            'organization_name' => $this->organization_name ?? null,
            'status' => ObjectStatusResource::make($this->objectStatus),
            'closed_at' => $this->closed_at,
            'lat' => $this->lat,
            'long' => $this->long,
            'deadline' => $this->deadline,
            'created_at' => $this->created_at,
            'regulation_count' => $this->regulations()->count(),
            'monitorings_count' => $this->monitorings()->count(),
            'paid' => $this->totalAmount(),
            'price_supervision_service' => $this->price_supervision_service,
            'inspector' => [
                'id' => $inspector ? $inspector->id : null,
                'name' =>  $inspector ? "{$inspector->surname} {$inspector->name} {$inspector->middle_name}" : null,
                'phone' =>  $inspector ? $inspector->phone : null,
            ],
        ];
    }

}
