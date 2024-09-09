<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatusEnum;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserStatusResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends BaseController
{
    public function login(): JsonResponse
    {

        $encodedData = request('token');
        $decodedData = base64_decode($encodedData);
        list($pinfl, $accessToken) = explode(':', $decodedData);

       $user = User::query()->where('pinfl', $pinfl)->first();
       if ($user){
           Auth::login($user);
           $user = Auth::user();
           if ($user->user_status_id != UserStatusEnum::ACTIVE) return $this->sendError('Kirish huquqi mavjud emas', code: 401);
           $roleId = request('role_id');
           $role = Role::query()->find($roleId);
           $token = JWTAuth::claims(['role_id' => $roleId])->fromUser($user);

           $success['token'] = $token;
           $success['full_name'] = $user->full_name;
           $success['pinfl'] = $user->pinfl;
           $success['role'] = new RoleResource($role);
           $success['status'] = new UserStatusResource($user->status);
           $success['region'] = $user->region_id ? new RegionResource($user->region) : null;
           $success['district'] = $user->district_id ?  new DistrictResource($user->district) : null;
           $success['image'] = $user->image ?  Storage::disk('public')->url($user->image): null;
           return $this->sendSuccess($success, 'User logged in successfully.');
       }else{
           return $this->sendError('Kirish huquqi mavjud emas', code: 401);
       }
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->sendSuccess(null,'Logged out successfully.');
    }

    public function auth(): JsonResponse
    {

        if (Auth::attempt(['login' => request('username'), 'password' => request('password'), ])) {
            $user = Auth::user();
            if ($user->user_status_id != UserStatusEnum::ACTIVE) return $this->sendError('Kirish huquqi mavjud emas', code: 401);
            $roleId = request('role_id');
            $role = Role::query()->find($roleId);
            $token = JWTAuth::claims(['role_id' => $roleId])->fromUser($user);

            $success['token'] = $token;
            $success['full_name'] = $user->full_name;
            $success['pinfl'] = $user->pinfl;
            $success['role'] = new RoleResource($role);
            $success['status'] = new UserStatusResource($user->status);
            $success['region'] = $user->region_id ? new RegionResource($user->region) : null;
            $success['district'] = $user->district_id ?  new DistrictResource($user->district) : null;
            $success['image'] = $user->image ?  Storage::disk('public')->url($user->image): null;

            return $this->sendSuccess($success, 'User logged in successfully.');
        }
        else{
            return $this->sendError('Unauthorised.', code: 401);
        }
    }

    public function refresh(): JsonResponse
    {
        $user = Auth::user();
        \request()->user()->token()->revoke();

        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;

        return $this->sendSuccess($success, 'User refreshed successfully.');
    }
}
