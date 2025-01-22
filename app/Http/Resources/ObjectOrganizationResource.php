<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObjectOrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $customer  = $this->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();

        return [
            'send_id' => $this->id,
            'send_date' => $this->created_at,
            'applicant_physical_name' => $customer ? $customer->name : null,
            'passport_applicant_physical' => null,
            'pinfl_applicant_physical' => $customer ? ($customer->name ? $customer->pinfl : null) : null,
            'tin_applicant_physical' =>  null,
            'address_physical' => '',
            'phone_applicant_physical' =>  $customer ? ($customer->name ? $customer->phone  : null) : null,
            'e_mail_physical' => null,
            'applicant_name' => $customer?->organization_name ?? null,
            'tin_applicant' => $customer ? ($customer->name ? null : $customer->tin) : null,
            'bank_number' => null,
            'economic_activity' => null,
            'address' => null,
            'phone_number' => null,
            'e_mail' => null,
            'object_name' => $this->name,
            'object_location' => $this->region?->name_uz.' '. $this->distict?->name_uz. ' ' . $this->location_building,
            'object_category' => $this->difficulty?->difficulty,
            'type_construction' => $this->construction_works ?? null,
            'construction_conclusion' => $this->number_protocol ?? null,
            'expertise_conclusion' => $this->reestr_number ?? null,
        ];
    }
}
