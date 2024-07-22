<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileEditRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends BaseController
{
    public function profile()
    {
        $user = Auth::guard('api')->user();

        $permissions = $user->getAllPermissions()->groupBy('group_name')->map(function ($group) {
            return [
                'group_name' => $group->first()->group_name,
                'permissions' => $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->values()->all(),
            ];
        })->values()->all();


        return $this->sendSuccess(new UserResource($user, $permissions), 'User found.');
    }

    public function edit(ProfileEditRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if ($request->post('phone')){
            $user->phone = $request->post('phone');
            $user->save();
        }
        if ($request->hasFile('image')){
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            $imagePath = $request->file('image')->store('users', 'public');
            $user->image = $imagePath;
            $user->save();
        }

        return $this->sendSuccess(new UserResource($user), 'User updated successfully.');
    }
}
