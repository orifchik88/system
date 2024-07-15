<?php

namespace App\Http\Resources;

use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

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
            'name' => $this->name,
            'phone' => $this->phone,
            'role' => $this->whenPivotLoaded('article_users', function () {
                $role = Role::find($this->pivot->role_id);
                return $role ? UserRoleEnum::getValueByKey($role->name) : null;
            }),
        ];
    }
}
