<?php

namespace App\Http\Resources;

use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArticleUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fish' => $this->full_name,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'phone_number' => $this->phone,
            'pinfl' => $this->pinfl,
            'identification_number' => $this->identification_number,
            'organization_name' => $this->organization_name,
            'role' => $this->whenPivotLoaded('article_users', function () {
                $role = Role::find($this->pivot->role_id);
                return RoleResource::make($role);
            }),
            'user_objects' => collect($this->roles)->map(function ($role) {
                $objects = $this->objects()
                    ->whereIn('object_status_id', [
                        ObjectStatusEnum::PROGRESS,
                        ObjectStatusEnum::FROZEN,
                        ObjectStatusEnum::SUSPENDED
                    ])
                    ->where('role_id', $role->id)
                    ->get();
                $objectList = in_array($role->id, [5,6,7])
                    ? $objects->map(function ($object) {
                        return [
                            'id' => $object->id,
                            'name' => $object->name,
                            'task_id' => $object->task_id,
                        ];
                    })->toArray()
                    : [];

                return [
                    'role_name' => $role->name,
                    'object_count' => $objects->count(),
                    'object_list' => $objectList,
                ];
            })->toArray()
        ];
    }
}
