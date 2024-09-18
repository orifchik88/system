<?php

namespace App\Http\Resources;

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
        ];
    }
}
