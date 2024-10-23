<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $object = Article::query()->find($this->gu_id);
        $oldUser = User::query()->find($this->content->additionalInfo->old_user_id);
        $newUser = User::query()->find($this->content->additionalInfo->new_user_id);
        $role = Role::query()->find($this->content->additionalInfo->new_role_id);
        return [
            'id' => $this->id,
            'object' => ArticleResource::make($object),
            'old_user' => UserResource::make($oldUser),
            'new_user' => UserResource::make($newUser),
            'role' => RoleResource::make($role),
            'status' => $this->content->status,
        ];
    }
}
