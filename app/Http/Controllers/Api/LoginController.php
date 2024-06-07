<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
{
    public function login(Request $request): JsonResponse
    {
        if (Auth::attempt(['login' => request('username'), 'password' => request('password')])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['name'] = $user->name;

            return $this->sendSuccess($success, 'User logged in successfully.');

        }
        else{
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised'], 401);
        }
    }

    public function logout()
    {
        $user = Auth::guard('api')->user();
        $user->token()->revoke();
        return $this->sendSuccess(null,'Logged out successfully.');
    }
}
