<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserResource extends JsonResource
{
//    public function __construct($resource, public  $permissions = null, public  $roleId = null)
//    {
//        parent::__construct($resource);
//    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'surname' => $this->surname,
            'name' => $this->name,
            'region' => RegionResource::make($this->region) ?? null,
            'district' => DistrictResource::make($this->district) ?? null,
            'middle_name' => $this->middle_name,
            'roles' => RoleResource::collection($this->roles),
            'count_objects' => $this->objects ?  $this->objects()->count() : null,
            'phone'=> $this->phone,
            'pinfl'=> $this->pinfl,
            'login' => $this->login,
            'status' => UserStatusResource::make($this->status),
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
        ];
    }
}
