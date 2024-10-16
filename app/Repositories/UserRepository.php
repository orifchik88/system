<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserRole;

class UserRepository
{
    public function findByPinfl($pinfl): ?User
    {
        return User::where('pinfl', $pinfl)->first();
    }

    public function createOrUpdate(array $data): User
    {
        $user = User::updateOrCreate(
            ['pinfl' => $data['pinfl']],
            $data
        );
        return $user;
    }

    public function attachRole(User $user, int $roleId): void
    {
        if (!UserRole::where('user_id', $user->id)->where('role_id', $roleId)->exists()) {
            UserRole::create(['user_id' => $user->id, 'role_id' => $roleId]);
        }
    }
}
