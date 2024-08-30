<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
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
            'type' => $this->type,
            'description' => $this->description
        ];
        return $data;
    }
}
