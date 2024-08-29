<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{

//    public function __construct($resource, protected $showPermissions)
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
        $data = [
            'id' => $this->id,
            'name' => UserRoleEnum::getValueByKey($this->name) ?? $this->name,
        ];


//        if ($this->showPermissions) {
//            $data['permissions'] = PermissionResource::collection($this->permissions);
//        }

        return $data;
    }
}
