<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObjectDesignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $customer = $this->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
        $designer = $this->users()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
        $builder = $this->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

        return [
            'send_id' => $this->id,
            'send_date' => $this->created_at,
            'customer_name'  => $customer ? ($customer->name ? $customer->full_name : $customer->organization_name) : null,
            'customer_address'  => null,
            'object_name' => $this->name,
            'object_address' => $this->region?->name_uz . ' ' . $this->distict?->name_uz . ' ' . $this->location_building,
            'start_date' =>  Carbon::parse($this->created_at)->format('Y-m-d'),
            'finish_date' => $this->deadline,
            'designer_name' => $designer ? ($designer->name ? $designer->full_name : $designer->organization_name) : null,
            'designer_address' => null,
            'constructor_name' => $builder ? ($builder->name ? $builder->full_name : $builder->organization_name) : null,
            'constructor_INN' => $builder->pinfl ?? null,
            'constructor_address' =>  null,
        ];


    }
}
