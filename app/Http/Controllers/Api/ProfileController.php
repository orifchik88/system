<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    public function profile()
    {
        $user = Auth::guard('api')->user();
        return $this->sendSuccess(UserResource::make($user), 'User found.');
    }
}
