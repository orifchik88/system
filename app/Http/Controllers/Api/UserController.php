<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\UserRequest;
use App\Http\Resources\UserStatusResource;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class UserController extends BaseController
{
    public function users(Request $request): JsonResponse
    {
        return $this->sendSuccess(User::all(), 'All Users');
    }

    public function create(UserRequest $request): JsonResponse
    {

    }

    public function edit(): JsonResponse
    {

    }

    public function status(): JsonResponse
    {
        return $this->sendSuccess(UserStatusResource::collection(UserStatus::all()), 'All User status');
    }
}
