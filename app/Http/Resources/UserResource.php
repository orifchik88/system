<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'surname' => $this->surname,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
            'address' => $this->address,
            'organization_name' => $this->organization_name,
            'phone'=> $this->phone,
            'nps'=> $this->nps,
            'login' => $this->login,
            'permissions' => PermissionResource::collection($this->getAllPermissions()),
        ];
    }
}
