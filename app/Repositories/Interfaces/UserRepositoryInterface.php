<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByPinfl($pinfl): ?User;
    public function createOrUpdate(array $data): User;
    public function attachRole(User $user, int $roleId): void;
}
