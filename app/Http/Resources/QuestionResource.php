<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $userRoles  = $request->user()->roles;
        $roles = collect($this->roles)->values();

        return [
            'id' => $this->id,
            'question' => $this->question,
            'roles' => $roles->filter(function ($role) use ($userRoles) {
                return !$userRoles->contains('id', $role['id']);
            })->map(function ($role) {
                return (new RoleResource((object) $role, false))->toArray(request());
            })->values(),
        ];
    }
}
